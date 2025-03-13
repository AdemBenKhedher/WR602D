<?php

namespace App\Controller;

use App\Entity\File;
use App\Service\HtmlToPdfService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints\File
as FileConstraint;

class HtmlController extends AbstractController
{
    private $pdfService;
    private $entityManager;

    public function __construct(HtmlToPdfService $pdfService, EntityManagerInterface $entityManager)
    {
        $this->pdfService = $pdfService;
        $this->entityManager = $entityManager;
    }

    #[Route('/html', name: 'generate_pdf')]
    public function generatePdf(Request $request): Response
    {
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
            ->add('submit', SubmitType::class, [
                'label' => 'Générer PDF'
            ])
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
                $htmlContent = file_get_contents($htmlFile->getPathname());

                $filename = uniqid('pdf_', true);
                $pdfPath = $this->pdfService->generatePdfFromHtml($htmlContent, $filename);

                $file = new File();
                $file->setName($filename . '.pdf');
                $file->setCreatedAt(new DateTimeImmutable());
                $file->setUser($this->getUser());
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
        ]);
    }
}
