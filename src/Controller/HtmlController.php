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
        $user = $this->getUser();
        $subscription = $user->getSubscription();

        if (!$subscription) {
            $this->addFlash('error', 'Vous devez avoir un abonnement actif pour générer un PDF. ');
            return $this->redirectToRoute('subscription_page');
        }

        $maxPdf = $subscription->getMaxPdf();
        $startOfDay = new DateTimeImmutable('today midnight');
        $endOfDay = new DateTimeImmutable('tomorrow midnight');
        $pdfsGeneratedToday = $this->fileRepository->countPdfGeneratedByUserOnDate(
            $user->getId(),
            $startOfDay,
            $endOfDay
        );

        $warningMessage = null;
        if ($pdfsGeneratedToday >= $maxPdf) {
            $warningMessage = 'Vous avez atteint la limite de génération de PDF pour aujourd\'hui. ';
            $warningMessage .= 'Pour augmenter cette limite, veuillez souscrire à un abonnement. ';
            $warningMessage .= '<a href="' . $this->generateUrl('subscription_page') . '"
            >Cliquez ici pour Améliorer votre abonnement</a>';
        }

        $form = $this->createFormBuilder()
            ->add('htmlFile', FileType::class, [
                'label' => 'Fichier HTML',
                'required' => true,
                'constraints' => [
                    new FileConstraint([
                        'mimeTypes' => ['text/html', 'text/plain', 'application/octet-stream'],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier HTML valide',
                    ])
                ],
            ])
            ->add('submit', SubmitType::class, ['label' => 'Générer PDF'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            try {
                /** @var UploadedFile $htmlFile */
                $htmlFile = $data['htmlFile'];
                if (!$htmlFile) {
                    $this->addFlash('error', 'Veuillez télécharger un fichier HTML');
                    return $this->redirectToRoute('generate_pdf');
                }

                if ($pdfsGeneratedToday >= $maxPdf) {
                    $this->addFlash('error', 'Vous avez atteint la limite de génération de PDF pour aujourd\'hui. ' .
                        'Veuillez souscrire à un abonnement pour générer davantage de PDF.');
                    return $this->redirectToRoute('subscription_page');
                }

                $htmlContent = file_get_contents($htmlFile->getPathname());
                $filename = uniqid('pdf_', true);
                $pdfPath = $this->pdfService->generatePdfFromHtml($htmlContent, $filename);

                $file = new File();
                $file->setName($filename . '.pdf');
                $file->setCreatedAt(new DateTimeImmutable());
                $file->setUser($user); // Associer à l'utilisateur
                $this->entityManager->persist($file);
                $this->entityManager->flush();

                return new Response(
                    file_get_contents($pdfPath),
                    200,
                    [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="' . $filename . '.pdf"'
                    ]
                );
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la génération du PDF: ' . $e->getMessage());
            }
        }

        return $this->render('html/generate_pdf.html.twig', [
            'form' => $form->createView(),
            'warningMessage' => $warningMessage,
        ]);
    }
}
