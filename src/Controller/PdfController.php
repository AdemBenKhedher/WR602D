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

            return $this->redirectToRoute('app_pdf_open_new_tab', ['url' => $url]);
        }

        $returnFromPdf = $request->query->get('returned');
        if ($returnFromPdf) {
            $this->addFlash('success', 'PDF généré avec succès! Vous pouvez générer un autre PDF.');
        }

        return $this->render('pdf/generate_pdf.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/pdf/display', name: 'app_pdf_display')]
    public function displayPdf(Request $request): Response
    {
        $url = $request->query->get('url');


        return $this->pdfService->generatePdfFromUrl($url);
    }

    #[Route('/pdf/return', name: 'app_pdf_return')]
    public function returnFromPdf(): Response
    {
        return $this->redirectToRoute('app_pdf', ['returned' => true]);
    }

    #[Route('/pdf/open-new-tab', name: 'app_pdf_open_new_tab')]
    public function openPdfNewTab(Request $request): Response
    {
        $url = $request->query->get('url');

        return $this->render('pdf/open_new_tab.html.twig', [
            'pdf_url' => $this->generateUrl('app_pdf_display', ['url' => $url])
        ]);
    }
}
