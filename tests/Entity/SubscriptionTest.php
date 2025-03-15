<?php

namespace App\Tests\Entity;

use App\Entity\Subscription;
use PHPUnit\Framework\TestCase;

class SubscriptionTest extends TestCase
{
    public function testGetterAndSetter()
    {
        $subscription = new Subscription();

        $name = "Premium Plan";
        $description = "Full access to all features";
        $maxPdf = 100;
        $price = 9.99;
        $specialPrice = 7.99;
        $specialPriceFrom = new \DateTimeImmutable('2024-01-01');
        $specialPriceTo = new \DateTimeImmutable('2024-12-31');

        $subscription->setName($name);
        $subscription->setDescription($description);
        $subscription->setMaxPdf($maxPdf);
        $subscription->setPrice($price);
        $subscription->setSpecialPrice($specialPrice);
        $subscription->setSpecialPriceFrom($specialPriceFrom);
        $subscription->setSpecialPriceTo($specialPriceTo);

        $this->assertEquals($name, $subscription->getName());
        $this->assertEquals($description, $subscription->getDescription());
        $this->assertEquals($maxPdf, $subscription->getMaxPdf());
        $this->assertEquals($price, $subscription->getPrice());
        $this->assertEquals($specialPrice, $subscription->getSpecialPrice());
        $this->assertEquals($specialPriceFrom, $subscription->getSpecialPriceFrom());
        $this->assertEquals($specialPriceTo, $subscription->getSpecialPriceTo());
    }
}
