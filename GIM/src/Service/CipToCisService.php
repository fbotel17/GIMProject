<?php

// src/Service/CipToCisService.php
namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class CipToCisService
{
    private $client;
    private $logger;

    public function __construct(HttpClientInterface $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    public function getCisFromCip(string $cip): ?string
    {
        $url = "https://base-donnees-publique.medicaments.gouv.fr/extrait.php?specid=$cip";

        $response = $this->client->request('GET', $url);
        $html = $response->getContent();

        $this->logger->info('HTML Content', ['html' => $html]);

        $crawler = new Crawler($html);

        // Vérifiez si le sélecteur trouve des éléments
        $cisNode = $crawler->filter('table.tableau tr:contains("CIS") td');
        if ($cisNode->count() > 0) {
            $cis = $cisNode->text();
            return $cis ?: null;
        } else {
            $this->logger->error('CIS not found in HTML', ['cip' => $cip]);
            return null;
        }
    }
}
