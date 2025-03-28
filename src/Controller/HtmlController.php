<?php

namespace App\Controller;

use App\Entity\File;
use App\Service\HtmlToPdfService;
use App\Repository\FileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints\File as FileConstraint;

class HtmlController extends AbstractController
{
    private $pdfService;
    private $entityManager;
    private $fileRepository;

    public function __construct(
        HtmlToPdfService $pdfService,
        EntityManagerInterface $entityManager,
        FileRepository $fileRepository
    ) {
        $this->pdfService = $pdfService;
        $this->entityManager = $entityManager;
        $this->fileRepository = $fileRepository;
    }

    #[Route('/html', name: 'generate_pdf')]
    public function generatePdf(Request $request): Response
    {
        if (!$this->checkSubscription()) {
            return $this->redirectToRoute('subscription_page');
        }

        $form = $this->createPdfForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processPdfGeneration($form->getData());
        }

        return $this->render('html/generate_pdf.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function checkSubscription(): bool
    {
        $user = $this->getUser();
        $subscription = $user->getSubscription();

        if (!$subscription) {
            $this->addFlash('error', 'Vous devez avoir un abonnement actif pour générer un PDF.');
            return false;
        }

        $maxPdf = $subscription->getMaxPdf();
        $startOfDay = new DateTimeImmutable('today midnight');
        $endOfDay = new DateTimeImmutable('tomorrow midnight');
        $pdfsGeneratedToday = $this->fileRepository->countPdfGeneratedByUserOnDate(
            $user->getId(),
            $startOfDay,
            $endOfDay
        );

        if ($pdfsGeneratedToday >= $maxPdf) {
            $this->addFlash('error', 'Vous avez atteint la limite de génération de PDF pour aujourdhui.');
            return false;
        }
        return true;
    }

    private function createPdfForm()
    {
        return $this->createFormBuilder()
            ->add('htmlFile', FileType::class, [
                'label' => 'Fichier HTML',
                'required' => false,
                'constraints' => [
                    new FileConstraint([
                        'mimeTypes' => ['text/html', 'text/plain', 'application/octet-stream'],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier HTML valide',
                    ])
                ],
            ])
            ->add('htmlCode', TextareaType::class, [
                'label' => 'Code HTML',
                'required' => false,
                'attr' => ['rows' => 10, 'placeholder' => 'Collez votre code HTML ici...']
            ])
            ->add('submit', SubmitType::class, ['label' => 'Générer et Envoyer PDF'])
            ->getForm();
    }

    private function processPdfGeneration(array $data): Response
    {
        try {
            $htmlContent = $this->extractHtmlContent($data);
            if (!$htmlContent) {
                $this->addFlash('error', 'Veuillez fournir un fichier HTML ou saisir du code HTML.');
                return $this->redirectToRoute('generate_pdf');
            }

            return $this->generateAndSavePdf($htmlContent);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la génération du PDF: ' . $e->getMessage());
            return $this->redirectToRoute('generate_pdf');
        }
    }

    private function extractHtmlContent(array $data): ?string
    {
        if ($data['htmlFile']) {
            /** @var UploadedFile $htmlFile */
            $htmlFile = $data['htmlFile'];
            return file_get_contents($htmlFile->getPathname());
        }
        if (!empty($data['htmlCode'])) {
            return $data['htmlCode'];
        }
        return null;
    }

    private function generateAndSavePdf(string $htmlContent): Response
    {
        $filename = uniqid('pdf_', true);
        $pdfPath = $this->pdfService->generatePdfFromHtml($htmlContent, $filename);

        $file = new File();
        $file->setName($filename . '.pdf');
        $file->setCreatedAt(new DateTimeImmutable());
        $file->setUser($this->getUser());
        $this->entityManager->persist($file);
        $this->entityManager->flush();

        $recipientEmail = $this->getUser()->getEmail();
        $this->pdfService->sendPdfByEmail(
            $pdfPath,
            $recipientEmail,
            'Votre PDF généré',
            'Voici le PDF que vous avez demandé.'
        );

        $this->addFlash('success', 'Le PDF a été généré et envoyé à votre adresse e-mail.
         Vous pouvez également le télécharger en cliquant sur le bouton ci-dessous.');

        return $this->render('html/pdf_success.html.twig', [
            'pdfPath' => $pdfPath,
            'filename' => $filename . '.pdf',
        ]);
    }
}
