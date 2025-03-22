<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Inventaire;
use App\Entity\Traitement;
use App\Repository\InventaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class InventaireController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    // Injection du EntityManagerInterface via le constructeur
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/inventaire', name: 'app_inventaire')]
    public function afficherInventaire(InventaireRepository $inventaireRepository, UserInterface $user, EntityManagerInterface $em)
    {

        $inventaire = $inventaireRepository->findBy(['user' => $user]);

        // Passer les données à la vue
        return $this->render('inventaire/index.html.twig', [
            'user' => $user,
            'inventaire' => $inventaire,
        ]);
    }

    #[Route('/delete-inventaire/{id}', name: 'delete_inventaire', methods: ['POST'])]
    public function deleteMedicaments(int $id, Request $request)
    {
        $inventaire = $this->entityManager->getRepository(Inventaire::class)->find($id);

        if (!$inventaire) {
            throw $this->createNotFoundException("L\'inventaire avec l'ID $id n'existe pas.");
        }

        // Vérifier le token CSRF pour éviter les suppressions malveillantes
        if (!$this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_medicament');
        }

        // Suppression de l'entité
        $this->entityManager->remove($inventaire);
        $this->entityManager->flush();

        $this->addFlash('success', 'Médicament supprimé de l\'inventaire.');

        return $this->redirectToRoute('app_inventaire');
    }

    #[Route('/consommer-inventaire/{id}', name: 'consommer_inventaire', methods: ['POST'])]
    public function consommerInventaire(int $id, Request $request)
    {
        $quantiteConsommee = $request->request->get('quantite_consommee');
        $inventaire = $this->entityManager->getRepository(Inventaire::class)->find($id);

        if ($inventaire && $quantiteConsommee > 0 && $quantiteConsommee <= $inventaire->getQuantite()) {
            $inventaire->setQuantite($inventaire->getQuantite() - $quantiteConsommee);
            $this->entityManager->flush();

            $this->addFlash('success', 'Quantité mise à jour avec succès.');
        } else {
            $this->addFlash('error', 'Quantité invalide ou médicament introuvable.');
        }

        return $this->redirectToRoute('app_inventaire');
    }

    #[Route('/ajouter-inventaire/{id}', name: 'ajouter_inventaire', methods: ['POST'])]
    public function ajouterInventaire(int $id, Request $request)
    {
        $quantiteAjoutee = $request->request->get('quantite_ajoutee');
        $inventaire = $this->entityManager->getRepository(Inventaire::class)->find($id);

        if ($inventaire && $quantiteAjoutee > 0) {
            $inventaire->setQuantite($inventaire->getQuantite() + $quantiteAjoutee);
            $this->entityManager->flush();

            $this->addFlash('success', 'Quantité mise à jour avec succès.');
        } else {
            $this->addFlash('error', 'Quantité invalide ou médicament introuvable.');
        }

        return $this->redirectToRoute('app_inventaire');
    }

    #[Route('/api/inventaire', name: 'api_inventaire', methods: ['GET'])]
    public function getInventaire(InventaireRepository $inventaireRepository, UserInterface $user, SerializerInterface $serializer): JsonResponse
    {
        $inventaire = $inventaireRepository->findBy(['user' => $user]);

        $jsonContent = $serializer->serialize($inventaire, 'json', ['groups' => 'inventaire']);

        return new JsonResponse($jsonContent, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/api/inventaire/{id}', name: 'api_delete_inventaire', methods: ['DELETE'])]
    public function deleteInventaire(int $id, Request $request): JsonResponse
    {
        $inventaire = $this->entityManager->getRepository(Inventaire::class)->find($id);

        if (!$inventaire) {
            return new JsonResponse(['error' => "L'inventaire avec l'ID $id n'existe pas."], JsonResponse::HTTP_NOT_FOUND);
        }

        // Vérifier le token CSRF pour éviter les suppressions malveillantes
        if (!$this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            return new JsonResponse(['error' => 'Token CSRF invalide.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->entityManager->remove($inventaire);
        $this->entityManager->flush();

        return new JsonResponse(['success' => 'Médicament supprimé de l\'inventaire.'], JsonResponse::HTTP_OK);
    }

    #[Route('/api/inventaire/consommer/{id}', name: 'api_consommer_inventaire', methods: ['POST'])]
    public function apiConsommerInventaire(int $id, Request $request): JsonResponse
    {
        $quantiteConsommee = $request->request->get('quantite_consommee');
        $inventaire = $this->entityManager->getRepository(Inventaire::class)->find($id);

        if ($inventaire && $quantiteConsommee > 0 && $quantiteConsommee <= $inventaire->getQuantite()) {
            $inventaire->setQuantite($inventaire->getQuantite() - $quantiteConsommee);
            $this->entityManager->flush();

            return new JsonResponse(['success' => 'Quantité mise à jour avec succès.'], JsonResponse::HTTP_OK);
        }

        return new JsonResponse(['error' => 'Quantité invalide ou médicament introuvable.'], JsonResponse::HTTP_BAD_REQUEST);
    }

    #[Route('/api/inventaire/ajouter/{id}', name: 'api_ajouter_inventaire', methods: ['POST'])]
    public function apiAjouterInventaire(int $id, Request $request): JsonResponse
    {
        $quantiteAjoutee = $request->request->get('quantite_ajoutee');
        $inventaire = $this->entityManager->getRepository(Inventaire::class)->find($id);

        if ($inventaire && $quantiteAjoutee > 0) {
            $inventaire->setQuantite($inventaire->getQuantite() + $quantiteAjoutee);
            $this->entityManager->flush();

            return new JsonResponse(['success' => 'Quantité mise à jour avec succès.'], JsonResponse::HTTP_OK);
        }

        return new JsonResponse(['error' => 'Quantité invalide ou médicament introuvable.'], JsonResponse::HTTP_BAD_REQUEST);
    }

    #[Route('/api/inventaire/search', name: 'api_search_inventaire', methods: ['GET'])]
    public function searchInventaire(Request $request, InventaireRepository $inventaireRepository, UserInterface $user, SerializerInterface $serializer): JsonResponse
    {
        $nom = $request->query->get('nom');
        if (!$nom) {
            return new JsonResponse(['error' => 'Le paramètre "nom" est requis.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $inventaire = $inventaireRepository->createQueryBuilder('i')
            ->join('i.medicament', 'm') // Joindre l'entité Medicament
            ->where('i.user = :user')
            ->andWhere('m.nom LIKE :nom')
            ->setParameter('user', $user)
            ->setParameter('nom', '%' . $nom . '%')
            ->getQuery()
            ->getResult();

        $jsonContent = $serializer->serialize($inventaire, 'json', ['groups' => 'inventaire']);

        return new JsonResponse($jsonContent, JsonResponse::HTTP_OK, [], true);
    }





}
