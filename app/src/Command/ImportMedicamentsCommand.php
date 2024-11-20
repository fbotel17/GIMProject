<?php


namespace App\Command;

use App\Entity\Medicament;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:import-medicaments',
    description: 'Importe les médicaments depuis un fichier .txt dans la base de données.',
)]
class ImportMedicamentsCommand extends Command
{
    private EntityManagerInterface $entityManager;

    // Injection du EntityManagerInterface dans le constructeur
    public function __construct(EntityManagerInterface $entityManager)
    {
        // Initialisation de la propriété $entityManager
        $this->entityManager = $entityManager;

        parent::__construct();
    }



    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '1024M');

        $filePath = 'public/CIS_bdpm_utf8.txt';

        if (!file_exists($filePath)) {
            $output->writeln('<error>Fichier non trouvé : ' . $filePath . '</error>');
            return Command::FAILURE;
        }

        $file = fopen($filePath, 'r');
        if (!$file) {
            $output->writeln('<error>Impossible d’ouvrir le fichier.</error>');
            return Command::FAILURE;
        }

        $lineCount = 0;

        while (($line = fgets($file)) !== false) {
            // Lire la ligne et convertir en UTF-8 si nécessaire
            $line = mb_convert_encoding($line, 'UTF-8', 'Windows-1252'); // Si le fichier est dans un autre encodage

            $columns = explode("\t", trim($line));

            if (count($columns) < 9) {
                $output->writeln("<error>Ligne ignorée (format incorrect) : $line</error>");
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

            // Convertir la date du format jour/mois/année (14/10/2021)
            $dateCommercialisation = \DateTime::createFromFormat('d/m/Y', $columns[7]);
            if ($dateCommercialisation === false) {
                $output->writeln("<error>Format de date invalide pour : $columns[7]</error>");
                continue;
            }
            $medicament->setDateCommercialisation($dateCommercialisation);

            $medicament->setFabricant($columns[8]);

            $this->entityManager->persist($medicament);

            $lineCount++;

            // Pour économiser de la mémoire, on peut envoyer les changements à la base de données par batch
            if ($lineCount % 100 === 0) {  // Exemple : chaque 100 lignes
                $this->entityManager->flush(); // Sauvegarder les données par batch
                $this->entityManager->clear(); // Libérer la mémoire des entités déjà persistées
            }
        }

        fclose($file);

        $this->entityManager->flush(); // Sauvegarder le reste des entités
        $output->writeln("<info>Importation terminée. $lineCount lignes insérées.</info>");

        return Command::SUCCESS;
    }
}
