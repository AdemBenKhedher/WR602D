<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\FileRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HistoryController extends AbstractController
{
    #[Route('/history', name: 'app_history')]
    public function index(FileRepository $fileRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour voir votre historique.');
            return $this->redirectToRoute('app_login');
        }
        $history = $fileRepository->getPdfHistoryByUser($user->getId());
        $totalCount = $fileRepository->countTotalPdfsGeneratedByUser($user->getId());


        return $this->render('history/index.html.twig', [
            'history' => $history,
            'totalCount' => $totalCount,

        ]);
    }
}
