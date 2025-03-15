<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use RuntimeException;
use Exception;

class PdfGeneratorService
{
    private HttpClientInterface $client;
    private string $gotenbergUrl;

    public function __construct(HttpClientInterface $client, ParameterBagInterface $params)
    {
        $this->client = $client;
        $this->gotenbergUrl = rtrim($params->get('gotenberg_url'), '/') . '/forms/chromium/convert/url';
    }

    public function generatePdfFromUrl(string $url): Response
    {
        try {
            $response = $this->client->request('POST', $this->gotenbergUrl, [
                'headers' => ['Content-Type' => 'multipart/form-data'],
                'body' => [
                    'url' => $url,
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                return new Response(
                    $response->getContent(),
                    200,
                    [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="generated.pdf"',
                    ]
                );
            }

            throw new RuntimeException('Erreur lors de la génération du PDF avec Gotenberg.');
        } catch (Exception $e) {
            throw new RuntimeException('Erreur lors de la communication avec Gotenberg: ' . $e->getMessage());
        }
    }
}
