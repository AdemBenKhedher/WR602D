<?php

namespace App\Controller;

use App\Service\SymfonyDocs;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SymfonyDocsController extends AbstractController
{
    private SymfonyDocs $symfonyDocs;

    public function __construct(SymfonyDocs $symfonyDocs)
    {
        $this->symfonyDocs = $symfonyDocs;
    }

    #[Route('/symfonyDocs', name: 'app_symfony_docs')]
    public function fetchGitHubInfo(): JsonResponse
    {
        $data = $this->symfonyDocs->fetchGitHubInformation();
        return $this->json($data);
    }
}
