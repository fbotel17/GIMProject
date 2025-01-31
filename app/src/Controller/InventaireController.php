<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\InventaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class InventaireController extends AbstractController
{
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
}
