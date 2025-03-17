<?php

// src/Controller/TraitementController.php
namespace App\Controller;

use GuzzleHttp\Client;
use App\Entity\Inventaire;
use App\Entity\Medicament;
use App\Entity\Traitement;
use App\Form\MedicamentType;
use App\Form\TraitementType;
use App\Repository\MedicamentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\InventaireRepository;
use App\Service\NotificationService;


class TraitementController extends AbstractController
{
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    #[Route('/traitement', name: 'app_traitement')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $traitements = $entityManager->getRepository(Traitement::class)->findBy(['user' => $user]);

        $traitementsData = [];
        foreach ($traitements as $traitement) {
            $traitementsData[] = [
                'traitement' => $traitement,
                'canToggleActif' => $this->canToggleActif($traitement, $entityManager),
            ];
        }

        return $this->render('traitement/index.html.twig', [
            'traitementsData' => $traitementsData,
        ]);
    }


    #[Route('/traitement/new', name: 'app_traitement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $traitement = new Traitement();
        $form = $this->createForm(TraitementType::class, $traitement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $traitement->setUser($this->getUser());
            $traitement->setActif(false);

            $dateRenouvellement = $traitement->getDateRenouvellement();
            $jourRestant = $dateRenouvellement ? $dateRenouvellement->diff(new \DateTime())->days : 0;

            $frequence = $traitement->getFrequence();
            $dose = $traitement->getDose();
            $totalDose = 0;

            if ($frequence && $dose) {
                if ($frequence === 'jour') {
                    $totalDose = $jourRestant * $dose;
                } elseif ($frequence === 'semaine') {
                    $totalDose = ceil($jourRestant / 7) * $dose;
                }
            }

            $entityManager->persist($traitement);
            $entityManager->flush();

            return $this->redirectToRoute('app_traitement');
        }


        return $this->render('traitement/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }





    #[Route('/traitement/{id}/add-medicament', name: 'app_traitement_add_medicament', methods: ['GET', 'POST'])]
    public function addMedicament(Traitement $traitement, Request $request, EntityManagerInterface $entityManager, MedicamentRepository $medicamentRepository, InventaireRepository $inventaireRepository): Response
    {
        $searchTerm = $request->query->get('search', '');
        $page = $request->query->getInt('page', 1);
        $limit = 25;
        $tab = $request->query->get('tab', 'inventaire'); // Par défaut, afficher les médicaments de l'inventaire

        if ($tab === 'inventaire') {
            // Récupérer les médicaments de l'inventaire de l'utilisateur
            $user = $this->getUser();
            $inventaires = $inventaireRepository->findMedicamentsBySearchTerm($user->getId(), $searchTerm, $page, $limit);

            $medicamentIds = array_map(fn($inventaire) => $inventaire->getMedicament()->getId(), $inventaires);
            $medicaments = $medicamentRepository->findBy(['id' => $medicamentIds]);

            $totalMedicaments = count($medicamentIds);
        } else {
            // Logique pour les médicaments globaux
            if (preg_match('/^\d{13}$/', $searchTerm)) {
                // Convertir le CIP7 en CIS via l'API GraphQL
                $cis = $this->queryGraphQL($searchTerm);

                // Si aucun CIS n'est trouvé
                if (!$cis) {
                    return $this->json(['error' => 'CIP non reconnu'], 404);
                }

                // Rechercher par CIS
                $medicaments = $medicamentRepository->findBySearchTerm($cis, $limit, ($page - 1) * $limit);
                $totalMedicaments = $medicamentRepository->countBySearchTerm($cis);
            } else {
                // Rechercher par nom ou codeCIS
                $medicaments = $medicamentRepository->findBySearchTerm($searchTerm, $limit, ($page - 1) * $limit);
                $totalMedicaments = $medicamentRepository->countBySearchTerm($searchTerm);
            }
        }

        $totalPages = ceil($totalMedicaments / $limit);

        if ($request->isMethod('POST')) {
            $medicamentId = $request->request->get('medicament_id');
            $medicament = $medicamentRepository->find($medicamentId);

            if ($medicament) {
                $traitement->addMedicament($medicament);
                $entityManager->persist($traitement);
                $entityManager->flush();
                $this->addFlash('success', message: 'Medicament : ' . $medicament->getNom() . ' ajouté au traitement ' . $traitement->getId());

                return $this->redirectToRoute('app_traitement_add_medicament', ['id' => $traitement->getId(), 'tab' => $tab]);
            }
        }

        return $this->render('traitement/add_medicament.html.twig', [
            'traitement' => $traitement,
            'medicaments' => $medicaments,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'searchTerm' => $searchTerm,
            'tab' => $tab,
        ]);
    }


    // Méthode pour interroger l'API GraphQL et récupérer le CIS
    private function queryGraphQL(string $cip7): ?string
    {
        $client = new Client();
        $query = [
            'query' => '
            query {
                presentations(CIP: ["' . $cip7 . '"]) {
                    CIP13
                    medicament {
                        CIS
                    }
                }
            }
        '
        ];

        try {
            // Effectuer l'appel à l'API GraphQL
            $response = $client->post('http://host.docker.internal:4000/graphql', [
                'json' => $query
            ]);

            // Récupérer la réponse
            $data = json_decode($response->getBody()->getContents(), true);

            // Vérifier si le CIS est présent dans la réponse
            if (isset($data['data']['presentations'][0]['medicament']['CIS'])) {
                return $data['data']['presentations'][0]['medicament']['CIS'];
            }

            // Si aucun CIS n'est trouvé
            return null;
        } catch (\Exception $e) {
            // Gérer les exceptions et erreurs
            return null;
        }
    }

    #[Route('/traitement/{id}/delete', name: 'app_traitement_delete', methods: ['POST'])]
    public function delete(Traitement $traitement, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($traitement);
        $entityManager->flush();

        $this->addFlash('success', 'Traitement supprimé avec succès.');

        return $this->redirectToRoute('app_traitement');
    }

    #[Route('/traitement/{id}/toggle-actif', name: 'app_traitement_toggle_actif', methods: ['POST'])]
    public function toggleActif(Traitement $traitement, EntityManagerInterface $entityManager): Response
    {
        $traitement->setActif(!$traitement->isActif());
        $entityManager->flush();

        $this->addFlash('success', 'État du traitement mis à jour avec succès.');

        return $this->redirectToRoute('app_traitement');
    }

    private function canToggleActif(Traitement $traitement, EntityManagerInterface $entityManager): bool
    {
        // Vérifier si le traitement a des médicaments associés
        if ($traitement->getMedicaments()->isEmpty()) {
            return false;
        }

        // Vérifier si les médicaments sont disponibles dans l'inventaire
        foreach ($traitement->getMedicaments() as $medicament) {
            $inventaire = $entityManager->getRepository(Inventaire::class)->findOneBy(['medicament' => $medicament, 'user' => $this->getUser()]);
            if (!$inventaire || $inventaire->getQuantite() <= 0) {
                return false;
            }
        }
        $this->notificationService->sendRenewalNotifications();

        return true;
    }
}
