<?php


// src/Service/MedicamentApiService.php
namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class MedicamentApiService
{
    private $client;
    private $logger;

    public function __construct(HttpClientInterface $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    public function searchMedicaments(string $searchTerm, int $limit, int $offset)
    {
        $query = <<<GRAPHQL
        query(\$searchTerm: String!, \$limit: Int!, \$offset: Int!) {
            searchMedicaments(searchTerm: \$searchTerm, limit: \$limit, offset: \$offset) {
                id
                name
                // Ajoutez d'autres champs selon vos besoins
            }
        }
        GRAPHQL;

        $variables = [
            'searchTerm' => $searchTerm,
            'limit' => $limit,
            'offset' => $offset,
        ];

        try {
            $response = $this->client->request('POST', 'https://api-bdpm-graphql.axel-op.fr/graphql', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    // Ajoutez d'autres en-têtes si nécessaire, comme l'authentification
                ],
                'json' => [
                    'query' => $query,
                    'variables' => $variables,
                ],
            ]);

            $this->logger->info('GraphQL Response', ['response' => $response->getContent(false)]);

            return $response->toArray();
        } catch (TransportExceptionInterface $e) {
            // Gérez les erreurs de requête ici
            $this->logger->error('GraphQL Request Error', ['exception' => $e]);
            throw new \Exception('Erreur lors de la requête à l\'API GraphQL: ' . $e->getMessage());
        }
    }
}
