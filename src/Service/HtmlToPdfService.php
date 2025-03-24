<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use RuntimeException;
use Exception;
use Twig\Environment;

class HtmlToPdfService
{
    private $client;
    private string $gotenbergUrl;
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(
        HttpClientInterface $client,
        ParameterBagInterface $params,
        MailerInterface $mailer,
        Environment $twig
    ) {
        $this->client = $client;
        $this->gotenbergUrl = rtrim($params->get('gotenberg_url'), '/') . '/forms/chromium/convert/html';
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function generatePdfFromHtml(string $htmlContent, string $filename): string
    {

        $tempDir = sys_get_temp_dir();
        $tempFilename = tempnam($tempDir, 'html_');
        file_put_contents($tempFilename, $htmlContent);

        try {
            $formFields = [
                'files' => new DataPart(fopen($tempFilename, 'r'), 'index.html', 'text/html')
            ];

            $formData = new FormDataPart($formFields);

            $headers = $formData->getPreparedHeaders()->toArray();
            $body = $formData->bodyToString();

            $response = $this->client->request('POST', $this->gotenbergUrl, [
                'headers' => $headers,
                'body' => $body
            ]);

            $pdfContent = $response->getContent();

            $pdfPath = '../public/Pdfs/' . $filename . '.pdf';
            file_put_contents($pdfPath, $pdfContent);

            unlink($tempFilename);

            return $pdfPath;
        } catch (\Exception $e) {
            unlink($tempFilename);
            throw $e;
        }
    }

    public function sendPdfByEmail(string $pdfPath, string $recipientEmail, string $subject, string $body): void
    {
        $htmlBody = $this->twig->render('email/pdf_email.html.twig'); // Render the email body from the Twig template

        $email = (new Email())
            ->from('no-reply@mmipdf.fr')
            ->to($recipientEmail)
            ->subject($subject)
            ->html($htmlBody) // Use the rendered Twig template as the email body
            ->attachFromPath($pdfPath, basename($pdfPath), 'application/pdf');

        $this->mailer->send($email);
    }
}
