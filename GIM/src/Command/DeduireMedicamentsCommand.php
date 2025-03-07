<?php

namespace App\Command;

use App\Entity\Inventaire;
use App\Entity\Traitement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:deduire-medicaments',
    description: 'Add a short description for your command',
)]
class DeduireMedicamentsCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $traitements = $this->entityManager->getRepository(Traitement::class)->findBy(['actif' => true]);

        foreach ($traitements as $traitement) {
            $user = $traitement->getUser();
            $frequence = $traitement->getFrequence();
            $dose = $traitement->getDose();
        
            if ($frequence === 'jour') {
                $this->deduireMedicaments($traitement, $dose);
            } elseif ($frequence === 'semaine') {
                $joursDePrise = $traitement->getJoursDePrise();
                $jourActuel = (new \DateTime())->format('N'); // 1 pour lundi, 2 pour mardi, etc.
        
                if (in_array($jourActuel, $joursDePrise)) {
                    $this->deduireMedicaments($traitement, $dose);
                }
            }
        }
        

        $this->entityManager->flush();
        $output->writeln('Inventaire mis à jour avec succès.');

        return Command::SUCCESS;
    }

    private function deduireMedicaments(Traitement $traitement, int $dose): void
    {
        foreach ($traitement->getMedicaments() as $medicament) {
            $inventaire = $this->entityManager->getRepository(Inventaire::class)->findOneBy([
                'user' => $traitement->getUser(),
                'medicament' => $medicament,
            ]);

            if ($inventaire) {
                $nouveauStock = max(0, $inventaire->getQuantite() - $dose);
                $inventaire->setQuantite($nouveauStock);
                $this->entityManager->persist($inventaire);
            }
        }
    }
}
