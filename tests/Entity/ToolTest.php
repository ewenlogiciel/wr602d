<?php

namespace App\Tests\Entity;

use App\Entity\Plan;
use App\Entity\Tool;
use PHPUnit\Framework\TestCase;

class ToolTest extends TestCase
{
    public function testGettersSetters(): void
    {
        $tool = new Tool();
        $tool->setName('URL vers PDF');
        $tool->setSlug('url');
        $tool->setIcon('fa-solid fa-globe');
        $tool->setDescription('Convertit une URL en PDF');
        $tool->setColor('#4a9eff');
        $tool->setIsActive(true);

        $this->assertEquals('URL vers PDF', $tool->getName());
        $this->assertEquals('url', $tool->getSlug());
        $this->assertEquals('fa-solid fa-globe', $tool->getIcon());
        $this->assertEquals('Convertit une URL en PDF', $tool->getDescription());
        $this->assertEquals('#4a9eff', $tool->getColor());
        $this->assertTrue($tool->isActive());
    }

    public function testAddAndRemovePlan(): void
    {
        $tool = new Tool();
        $plan = new Plan();
        $plan->setName('FREE');

        $tool->addPlan($plan);
        $this->assertCount(1, $tool->getPlan());
        $this->assertTrue($tool->getPlan()->contains($plan));

        $tool->removePlan($plan);
        $this->assertCount(0, $tool->getPlan());
    }

    public function testAddPlanDoesNotDuplicate(): void
    {
        $tool = new Tool();
        $plan = new Plan();

        $tool->addPlan($plan);
        $tool->addPlan($plan);

        $this->assertCount(1, $tool->getPlan());
    }

    public function testIsActiveDefaultsNull(): void
    {
        $tool = new Tool();
        $this->assertNull($tool->isActive());
    }
}
