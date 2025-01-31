<?php

namespace App\Controller;

use GuzzleHttp\Client;
use App\Entity\Inventaire;
use App\Entity\Medicament;
use Symfony\Component\Process\Process;
use App\Repository\MedicamentRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Command\ImportMedicamentsCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MedicamentController extends AbstractController
{

    private EntityManagerInterface $entityManager;

    // Injection du EntityManagerInterface via le constructeur
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    #[Route('/medicament', name: 'app_medicament')]
    public function index(Request $request, MedicamentRepository $medicamentRepository): Response
    {
        // Récupérer le terme de recherche s'il existe
        $searchTerm = $request->query->get('search', '');

        // Le numéro de la page, avec une valeur par défaut de 1 si non spécifiée
        $page = $request->query->getInt('page', 1);

        // Nombre d'éléments par page
        $limit = 25;

        // Vérification si le terme de recherche est un CIP7 valide
        if (preg_match('/^\d{7}$/', $searchTerm)) {
            // Convertir le CIP7 en CIS via l'API GraphQL
            $cis = $this->queryGraphQL($searchTerm);

            // Si aucun CIS n'est trouvé
            if (!$cis) {
                return $this->json(['error' => 'CIP non reconnu'], 404);
            }


            // Si un CIS est trouvé, on utilise le CIS pour effectuer la recherche dans la base de données
            $medicaments = $medicamentRepository->findBySearchTerm($cis, $limit, ($page - 1) * $limit);
            $totalMedicaments = $medicamentRepository->countBySearchTerm($cis);
        } else {
            // Recherche par nom ou codeCIS
            $medicaments = $medicamentRepository->findBySearchTerm($searchTerm, $limit, ($page - 1) * $limit);
            $totalMedicaments = $medicamentRepository->countBySearchTerm($searchTerm);
        }

        // Calcul du nombre total de pages
        $totalPages = ceil($totalMedicaments / $limit);

        return $this->render('medicament/index.html.twig', [
            'medicaments' => $medicaments,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'searchTerm' => $searchTerm,  // On passe le terme de recherche à la vue
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
                    CIP7
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
            return $e;
        }
    }


    #[Route('/import-medicaments', name: 'import_medicaments')]
    public function importMedicaments(Request $request)
    {
        ini_set('memory_limit', '1024M');

        // Utiliser le chemin absolu vers le fichier
        $filePath = $this->getParameter('kernel.project_dir') . '/public/CIS_bdpm_utf8.txt';

        if (!file_exists($filePath)) {
            return new JsonResponse(['error' => 'Fichier non trouvé : ' . $filePath], 404);
        }

        $file = fopen($filePath, 'r');
        if (!$file) {
            return new JsonResponse(['error' => 'Impossible d’ouvrir le fichier.'], 500);
        }

        $lineCount = 0;

        while (($line = fgets($file)) !== false) {
            $line = trim($line);
            $columns = explode("\t", $line);

            if (count($columns) < 9) {
                continue;
            }

            $medicament = new Medicament();
            $medicament->setCodeCIS($columns[0]);
            $medicament->setNom($columns[1]);
            $medicament->setFormePharmaceutique($columns[2]);
            $medicament->setVoieAdministration($columns[3]);
            $medicament->setEtatAutorisation($columns[4]);
            $medicament->setProcedure($columns[5]);
            $medicament->setEtatCommercialisation($columns[6]);

            $dateCommercialisation = \DateTime::createFromFormat('d/m/Y', $columns[7]);
            if ($dateCommercialisation === false) {
                continue;
            }
            $medicament->setDateCommercialisation($dateCommercialisation);

            if (isset($columns[8]) && trim($columns[8]) === "Warning disponibilité") {
                $possibleFabricant = trim($columns[10] ?? '');
            } else {
                $possibleFabricant = trim($columns[9] ?? '');
            }

            if (preg_match('/^EU\/\d+\/\d+$/', $possibleFabricant)) {
                $possibleFabricant = trim($columns[10] ?? '');
            }

            $medicament->setFabricant($possibleFabricant);

            $this->entityManager->persist($medicament);

            $lineCount++;

            if ($lineCount % 100 === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }

        fclose($file);

        $this->entityManager->flush();

        $this->addFlash('success', 'Importation terminée. ' . $lineCount . ' lignes insérées.');

        return $this->redirectToRoute('app_medicament');
    }

    #[Route('/delete-medicaments', name: 'delete_medicaments')]
    public function deleteMedicaments(Request $request)
    {
        ini_set('memory_limit', '1024M');

        $medicaments = $this->entityManager->getRepository(Medicament::class)->findAll();

        // Supprimer chaque médicament
        foreach ($medicaments as $medicament) {
            $this->entityManager->remove($medicament);
        }

        // Sauvegarder les changements
        $this->entityManager->flush();

        $this->addFlash('success', 'Tous les médicaments ont été supprimés.');


        return $this->redirectToRoute('app_medicament');
    }

    #[Route('/medicament/{id}/ajouter', name: 'medicament_ajouter')]
    public function ajouterInventaire(int $id, Request $request, MedicamentRepository $medicamentRepository, EntityManagerInterface $em, UserInterface $user): Response
    {
        // Récupérer le médicament par son ID
        $medicament = $medicamentRepository->find($id);

        if (!$medicament) {
            $this->addFlash('error', 'Médicament non trouvé.');
            return $this->redirectToRoute('app_medicament');
        }

        // Récupérer la quantité à ajouter depuis la requête
        $quantite = $request->request->get('quantite');

        if ($quantite <= 0) {
            $this->addFlash('error', 'La quantité doit être un nombre positif.');
            return $this->redirectToRoute('app_medicament');
        }

        // Créer un nouvel objet Inventaire
        $inventaire = new Inventaire();
        $inventaire->setUser($user);
        $inventaire->setMedicament($medicament);
        $inventaire->setQuantite($quantite);

        // Enregistrer dans la base de données
        $em->persist($inventaire);
        $em->flush();

        $this->addFlash('success', 'Médicament ajouté à l\'inventaire avec succès.');

        return $this->redirectToRoute('app_medicament');
    }
}
