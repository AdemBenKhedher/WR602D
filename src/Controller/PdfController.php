<?php

namespace App\Controller;

use App\Service\PdfGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PdfController extends AbstractController
{
    private PdfGeneratorService $pdfService;

    public function __construct(PdfGeneratorService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    #[Route('/pdf', name: 'app_pdf', methods: ['GET', 'POST'])]
    public function generatePdf(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('url', null, ['required' => true])
            ->setMethod('POST')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $url = $form->getData()['url'];

            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $this->addFlash('danger', 'URL invalide.');
                return $this->redirectToRoute('app_pdf');
            }

            return $this->redirectToRoute('app_pdf_display', ['url' => $url]);
        }

        return $this->render('pdf/generate_pdf.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/pdf/display', name: 'app_pdf_display')]
    public function displayPdf(Request $request): Response
    {
        $url = $request->query->get('url');

        // Génère et retourne directement le PDF (ouvre dans le navigateur)
        return $this->pdfService->generatePdfFromUrl($url);
    }
}
