<?php

namespace App\Controller;

use App\Entity\Inventaire;
use App\Entity\User;
use App\Entity\Traitement;
use App\Repository\InventaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
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



}
