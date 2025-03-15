<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\SubscriptionRepository;
use App\Repository\FileRepository;
use DateTimeImmutable;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    private Security $security;
    private SubscriptionRepository $subscriptionRepo;
    private FileRepository $fileRepository;

    public function __construct(Security $security, SubscriptionRepository $subscriptionRepo, FileRepository $fileRepository)
    {
        $this->security = $security;
        $this->subscriptionRepo = $subscriptionRepo;
        $this->fileRepository = $fileRepository;
    }

    public function getGlobals(): array
    {
        $user = $this->security->getUser();
        $userSubscription = $user ? $user->getSubscription() : null;

        // Calcul du début et de la fin de la journée
        $startOfDay = new DateTimeImmutable('today midnight');
        $endOfDay = new DateTimeImmutable('tomorrow midnight -1 second');

        $pdfCount = $user ? $this->fileRepository->countPdfGeneratedByUserOnDate($user->getId(), $startOfDay, $endOfDay) : 0;

        return [
            'user_subscription' => $userSubscription,
            'count' => $pdfCount,
        ];
    }
}
