<?php

namespace App\Controller;

use App\Service\HtmlToPdfService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;

class HtmlController extends AbstractController
{
    private $pdfService;

    public function __construct(HtmlToPdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    #[Route('/html', name: 'generate_pdf')]
    public function generatePdf(Request $request): Response
    {
        // Créer le formulaire simplifié pour l'upload de fichier HTML uniquement
        $form = $this->createFormBuilder()
            ->add('htmlFile', FileType::class, [
                'label' => 'Fichier HTML',
                'required' => true,
                'constraints' => [
                    new File([
                        'mimeTypes' => ['text/html', 'text/plain', 'application/octet-stream'],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier HTML valide',
                    ])
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Générer PDF'
            ])
            ->getForm();

        // Gérer la soumission du formulaire
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
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
                $pdf = $this->pdfService->generatePdfFromHtml($htmlContent);

                if ($pdf) {
                    // Retourner directement le PDF dans la réponse HTTP
                    return new Response(
                        $pdf,
                        200,
                        [
                            'Content-Type' => 'application/pdf',
                            'Content-Disposition' => 'inline; filename="generated.pdf"'
                        ]
                    );
                }
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la génération du PDF: ' . $e->getMessage());
            }
        }

        // Afficher le formulaire si non soumis ou invalide
        return $this->render('html/generate_pdf.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
