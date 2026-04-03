<?php

namespace App\Tests\Entity;

use App\Entity\Plan;
use PHPUnit\Framework\TestCase;

class PlanTest extends TestCase
{
    public function testGettersSetters(): void
    {
        $plan = new Plan();
        $plan->setName('BASIC');
        $plan->setDescription('Abonnement basic');
        $plan->setPrice(9.9);
        $plan->setUsageLimit(20);
        $plan->setRole('ROLE_BASIC');
        $plan->setStripePriceId('price_xxx');
        $plan->setActive(true);

        $this->assertEquals('BASIC', $plan->getName());
        $this->assertEquals('Abonnement basic', $plan->getDescription());
        $this->assertEquals(9.9, $plan->getPrice());
        $this->assertEquals(20, $plan->getUsageLimit());
        $this->assertEquals('ROLE_BASIC', $plan->getRole());
        $this->assertEquals('price_xxx', $plan->getStripePriceId());
        $this->assertTrue($plan->isActive());
    }

    public function testFreePlanHasNoStripePriceId(): void
    {
        $plan = new Plan();
        $plan->setName('FREE');
        $plan->setPrice(0);

        $this->assertNull($plan->getStripePriceId());
    }

    public function testUnlimitedPlanHasNullUsageLimit(): void
    {
        $plan = new Plan();
        $plan->setUsageLimit(null);

        $this->assertNull($plan->getUsageLimit());
    }
}
