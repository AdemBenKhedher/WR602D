<?php

namespace App\Controller;

use App\Repository\FileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use DateTimeImmutable;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_Home')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function countPdf(FileRepository $fileRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour voir cette information.');
            return $this->redirectToRoute('app_login');
        }
        $startOfDay = new DateTimeImmutable('today 00:00:00');
        $endOfDay = new DateTimeImmutable('today 23:59:59');
        $count = $fileRepository->countPdfGeneratedByUserOnDate($user->getId(), $startOfDay, $endOfDay);
        return $this->render('home/index.html.twig', [
            'count' => $count,
        ]);
    }
}
