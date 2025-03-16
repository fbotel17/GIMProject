<?php

// src/Service/NotificationService.php
namespace App\Service;

use App\Entity\Notifications;
use App\Entity\Traitement;
use App\Entity\Inventaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class NotificationService
{
    private $entityManager;
    private $mailer;

    public function __construct(EntityManagerInterface $entityManager, MailerInterface $mailer)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
    }

    public function sendRenewalNotifications()
    {
        $traitements = $this->entityManager->getRepository(Traitement::class)->findAll();

        foreach ($traitements as $traitement) {
            if ($traitement->isActif()) {
                $dateRenouvellement = $traitement->getDateRenouvellement();
                $dateNotification = (clone $dateRenouvellement)->modify('-5 days');

                if ($dateNotification <= new \DateTime()) {
                    $quantitesRestantes = $this->calculerQuantitesRestantes($traitement);
                    $this->envoyerNotification($traitement, $quantitesRestantes);
                }
            }
        }
    }

    private function calculerQuantitesRestantes(Traitement $traitement)
    {
        $quantitesRestantes = [];
        foreach ($traitement->getMedicaments() as $medicament) {
            $inventaire = $this->entityManager->getRepository(Inventaire::class)->findOneBy(['user' => $traitement->getUser(), 'medicament' => $medicament]);

            if (!$inventaire) {
                $quantitesRestantes[$medicament->getId()] = 0;
                continue;
            }

                        // Récupérer la dose et la fréquence
            $dose = (int)$traitement->getDose();
            $frequence = $traitement->getFrequence();

            // Initialiser la variable pour la dose totale
            $doseTotale = 0;

            // Vérifier la fréquence et ajuster le calcul de la dose totale
            if ($frequence === "jour") {
                $doseTotale = $dose; // Par défaut, si c'est "jour", la dose est déjà par jour
            } elseif ($frequence === "semaine") {
                $doseTotale = $dose * 7; // Si c'est "semaine", multiplier par 7 jours
            } else {
                // Si la fréquence est autre chose que "jour" ou "semaine", gérer le cas d'erreur
                // Par exemple, lancer une exception ou affecter une valeur par défaut
                $doseTotale = 0; // Ou une autre valeur par défaut
            }

            // Maintenant, tu peux utiliser $doseTotale pour les calculs suivants
            $joursRestants = $traitement->getDateRenouvellement()->diff(new \DateTime())->days;
            $quantiteConsommee = $doseTotale * $joursRestants;

            $quantitesRestantes[$medicament->getId()] = $inventaire->getQuantite() - $quantiteConsommee;
        }

        return $quantitesRestantes;
    }

    private function envoyerNotification(Traitement $traitement, array $quantitesRestantes)
    {
        $notification = new Notifications();
        $notification->setUser($traitement->getUser());
        $notification->setTraitement($traitement);
        $notification->setDateNotification(new \DateTime());

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        $emailContent = 'Vous aurez besoin de renouveler votre traitement bientôt. Quantités restantes : ' . PHP_EOL;
        foreach ($quantitesRestantes as $medicamentId => $quantiteRestante) {
            $emailContent .= '- Médicament ID ' . $medicamentId . ': ' . $quantiteRestante . PHP_EOL;
        }

        $email = (new Email())
            ->from('gim.project.insset@gmail.com')
            ->to($traitement->getUser()->getEmail())
            ->subject('Renouvellement de votre traitement')
            ->text($emailContent);

        $this->mailer->send($email);
    }
}
