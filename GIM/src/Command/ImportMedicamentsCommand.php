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
            // Assure-toi que le fichier est déjà en UTF-8, donc aucune conversion n'est nécessaire.
            // $line = mb_convert_encoding($line, 'UTF-8', 'Windows-1252');  // Enlève cette ligne.
            $line = trim($line);  // Enlève les espaces inutiles.

            // Afficher la ligne lue pour déboguer.
            // $output->writeln("<info>Ligne lue : $line</info>");

            $columns = explode("\t", $line);

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

            // Vérifie si la colonne 8 contient "Warning disponibilité"
            if (isset($columns[8]) && trim($columns[8]) === "Warning disponibilité") {
                $possibleFabricant = trim($columns[10] ?? '');
            } else {
                $possibleFabricant = trim($columns[9] ?? '');
            }

            // Vérifie que ce n'est pas un code de type "EU/1/..."
            if (preg_match('/^EU\/\d+\/\d+$/', $possibleFabricant)) {
                $possibleFabricant = trim($columns[10] ?? ''); // Prendre la colonne suivante si c'est un code
            }

            // Assigne le fabricant final
            $medicament->setFabricant($possibleFabricant);

            // Ajouter la persistance de l'entité
            $this->entityManager->persist($medicament);

            $lineCount++;

            // Pour économiser de la mémoire, on peut envoyer les changements à la base de données par batch
            if ($lineCount % 100 === 0) {  // Exemple : chaque 100 lignes
                $this->entityManager->flush(); // Sauvegarder les données par batch
                $this->entityManager->clear(); // Libérer la mémoire des entités déjà persistées
            }
        }

        fclose($file);

        // Sauvegarder les dernières entités restantes
        $this->entityManager->flush();
        $output->writeln("<info>Importation terminée. $lineCount lignes insérées.</info>");

        return Command::SUCCESS;
    }
}
