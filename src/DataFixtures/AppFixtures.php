<?php

namespace App\DataFixtures;

use App\Entity\Subscription;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator as FakerGenerator;
use DateTimeImmutable;

class AppFixtures extends Fixture
{
    private $faker;

    public function __construct(FakerGenerator $faker)
    {
        $this->faker = $faker;
    }

    public function load(ObjectManager $manager): void
    {
        $subscriptionNames = ['Premium', 'Gold', 'Beta'];
        $descriptions = [
            'Premium' => 'L\'abonnement premium vous offre un accès complet
             à toutes les fonctionnalités de la plateforme,
             avec un maximum de documents disponibles et des avantages exclusifs.',
            'Gold' => 'L\'abonnement gold offre une expérience haut de gamme,
             avec une sélection plus large de fonctionnalités et un accès prioritaire à certaines options.',
            'Beta' => 'L\'abonnement beta vous permet de tester les nouvelles fonctionnalités à un prix réduit,
             avec un accès limité à certaines options.'
        ];
        $maxPdfValues = [15, 5, 3];
        $prices = [100, 70, 40];

        for ($i = 0; $i < 3; $i++) {
            $price = $prices[$i];
            $specialPrice = round($price * 0.60, 2);

            $subscription = new Subscription();
            $subscription->setName($subscriptionNames[$i])
                ->setDescription($descriptions[$subscriptionNames[$i]])
                ->setMaxPdf($maxPdfValues[$i])
                ->setPrice($price)
                ->setSpecialPrice($specialPrice);

            $today = new DateTimeImmutable();
            $specialPriceFrom = $today->modify('+1 day');

            // Suppression du bloc else, et gestion des prix spéciaux
            if ($i === 0) {
                $specialPriceTo = $specialPriceFrom->modify('+60 days');
            }
            if ($i === 1) {
                $specialPriceTo = $specialPriceFrom->modify('+45 days');
            }
            if ($i === 2) {
                $specialPriceTo = $specialPriceFrom->modify('+30 days');
            }

            $subscription->setSpecialPriceFrom($specialPriceFrom)
                ->setSpecialPriceTo($specialPriceTo);

            $manager->persist($subscription);
        }

        $manager->flush();
    }
}
