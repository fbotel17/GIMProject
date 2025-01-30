<?php

namespace App\Controller;

use App\Repository\MedicamentRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(MedicamentRepository $medicamentRepository): Response
    {
        $medicaments = $medicamentRepository->findBy([], ['id' => 'DESC'], 100);

        return $this->render('home/index.html.twig', [
            'medicaments' => $medicaments,
        ]);
    }
}
