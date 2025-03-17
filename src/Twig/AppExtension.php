<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Subscription;
use App\Repository\SubscriptionRepository;
use App\Repository\FileRepository;
use DateTimeImmutable;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    private Security $security;
    private SubscriptionRepository $subscriptionRepo;
    private FileRepository $fileRepository;
    private ManagerRegistry $doctrine;

    public function __construct(
        Security $security,
        SubscriptionRepository $subscriptionRepo,
        ManagerRegistry $doctrine,
        FileRepository $fileRepository
    ) {
        $this->security = $security;
        $this->subscriptionRepo = $subscriptionRepo;
        $this->fileRepository = $fileRepository;
        $this->doctrine = $doctrine;
    }

    public function getGlobals(): array
    {
        $user = $this->security->getUser();
        $userSubscription = $user ? $user->getSubscription() : null;
        $startOfDay = new DateTimeImmutable('today midnight');
        $endOfDay = new DateTimeImmutable('tomorrow midnight -1 second');
        $pdfCount = $user
            ? $this->fileRepository->countPdfGeneratedByUserOnDate(
                $user->getId(),
                $startOfDay,
                $endOfDay
            )
            : 0;
        $subscriptions = $this->doctrine->getRepository(Subscription::class)->findAll();
        return [
            'user_subscription' => $userSubscription,
            'count' => $pdfCount,
            'subscriptions' => $subscriptions,
        ];
    }
}
