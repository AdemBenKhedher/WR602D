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
    public function countPdf(FileRepository $fileRepository): Response
    {

        return $this->render('home/index.html.twig', [
        ]);
    }
}
