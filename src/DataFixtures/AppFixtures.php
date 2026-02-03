<?php

namespace App\DataFixtures;

use App\Entity\Plan;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Plan gratuit
        $plan = new Plan();
        $plan->setName("FREE");
        $plan->setDescription("Abonnement gratuit");
        $plan->setPrice(0);
        $plan->setUsageLimit(2);
        $plan->setRole("ROLE_FREE");
        $plan->setActive(true);
        $manager->persist($plan);

        // Plan basic
        $plan = new Plan();
        $plan->setName("BASIC");
        $plan->setDescription("Abonnement basic : 20 générations par jour");
        $plan->setPrice(9.9);
        $plan->setUsageLimit(20);
        $plan->setRole("ROLE_BASIC");
        $plan->setActive(true);
        $manager->persist($plan);

        // Plan premium
        $plan = new Plan();
        $plan->setName("PREMIUM");
        $plan->setDescription("Abonnement PREMIUM : 200 générations par jour");
        $plan->setPrice(45);
        $plan->setUsageLimit(200);
        $plan->setRole("ROLE_PREMIUM");
        $plan->setActive(true);
        $manager->persist($plan);


        $manager->flush();
    }


}
