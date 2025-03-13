<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use RuntimeException;
use Exception;

class HtmlToPdfService
{
    private $client;
    private string $gotenbergUrl;

    public function __construct(HttpClientInterface $client, ParameterBagInterface $params)
    {
        $this->client = $client;
        $this->gotenbergUrl = rtrim($params->get('gotenberg_url'), '/') . '/forms/chromium/convert/html';

        
    }

    public function generatePdfFromHtml(string $htmlContent, string $filename): string
    {
        // Endpoint de Gotenberg pour la conversion HTML

        // Créer un fichier temporaire pour le HTML
        $tempDir = sys_get_temp_dir();
        $tempFilename = tempnam($tempDir, 'html_');
        file_put_contents($tempFilename, $htmlContent);

        try {
            // Créer un objet FormDataPart pour l'envoi multipart/form-data
            $formFields = [
                'files' => new DataPart(fopen($tempFilename, 'r'), 'index.html', 'text/html')
            ];

            $formData = new FormDataPart($formFields);

            // Obtenir les en-têtes et le corps de la requête
            $headers = $formData->getPreparedHeaders()->toArray();
            $body = $formData->bodyToString();

            // Envoyer la requête à Gotenberg
            $response = $this->client->request('POST', $this->gotenbergUrl, [
                'headers' => $headers,
                'body' => $body
            ]);

            // Récupérer le contenu du PDF généré
            $pdfContent = $response->getContent();

            // Enregistrer le PDF dans le répertoire public/pdfs
            $pdfPath = '../public/Pdfs/' . $filename . '.pdf';
            file_put_contents($pdfPath, $pdfContent);

            // Nettoyage du fichier temporaire
            unlink($tempFilename);

            // Retourner le chemin du PDF
            return $pdfPath;
        } catch (\Exception $e) {
            // Assurer le nettoyage même en cas d'erreur
            unlink($tempFilename);
            throw $e;
        }
    }
}
