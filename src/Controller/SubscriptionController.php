<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SubscriptionController extends AbstractController
{
    #[Route('/subscription', name: 'subscription_page')]
    public function subscriptionPage(ManagerRegistry $doctrine, Request $request)
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $subscriptions = $doctrine->getRepository(Subscription::class)->findAll();

        $basicSubscription = new Subscription();
        $basicSubscription->setName('Basique')
            ->setDescription('Offre gratuite avec un accès limité aux fonctionnalités.')
            ->setPrice(0)
            ->setMaxPdf(0);
        $subscriptions[] = $basicSubscription;

        return $this->render('subscription/index.html.twig', [
            'subscriptions' => $subscriptions,
            'user_subscription' => $user->getSubscription(),
        ]);
    }
    #[Route('/change-subscription/{subscriptionId}', name: 'change_subscription')]
    public function changeSubscription($subscriptionId, ManagerRegistry $doctrine)
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        if ($subscriptionId == 'basic') {
            $user->setSubscription(null);
            $doctrine->getManager()->flush();
            $this->addFlash('success', 'Abonnement réinitialisé avec succès.');
            return $this->redirectToRoute('subscription_page');
        }
        $subscription = $doctrine->getRepository(Subscription::class)->find($subscriptionId);
        if (!$subscription) {
            $this->addFlash('error', 'Abonnement non trouvé.');
            return $this->redirectToRoute('subscription_page');
        }
        $user->setSubscription($subscription);
        $doctrine->getManager()->flush();
        $this->addFlash('success', 'Abonnement mis à jour avec succès.');
        return $this->redirectToRoute('subscription_page');
    }
}
