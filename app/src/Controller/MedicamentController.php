<?php

namespace App\Controller;

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

        // Si un terme de recherche est spécifié, on filtre les résultats
        if ($searchTerm) {
            $medicaments = $medicamentRepository->findBySearchTerm($searchTerm, $limit, ($page - 1) * $limit);
            $totalMedicaments = $medicamentRepository->countBySearchTerm($searchTerm);
        } else {
            $medicaments = $medicamentRepository->findBy([], ['id' => 'DESC'], $limit, ($page - 1) * $limit);
            $totalMedicaments = count($medicamentRepository->findAll());
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
}
