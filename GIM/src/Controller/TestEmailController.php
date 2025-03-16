<?php

namespace App\Controller;

use App\Service\SendService;
use App\Service\NotificationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TestEmailController extends AbstractController
{
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    
    #[Route('/test-email', name: 'test_email')]
    public function testNotifications(): Response
    {
        $this->notificationService->sendRenewalNotifications();

        return new Response('Notifications de renouvellement envoyées !');
    }
}

