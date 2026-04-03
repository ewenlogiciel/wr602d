<?php

namespace App\Tests\Twig;

use App\Entity\Plan;
use App\Entity\User;
use App\Repository\GenerationRepository;
use App\Twig\UsageExtension;
use PHPUnit\Framework\TestCase;

class UsageExtensionTest extends TestCase
{
    private function makeExtension(int $used): UsageExtension
    {
        $repo = $this->createMock(GenerationRepository::class);
        $repo->method('countByUserOnDate')->willReturn($used);
        return new UsageExtension($repo);
    }

    public function testGetFunctionsReturnsDailyUsage(): void
    {
        $ext = $this->makeExtension(0);
        $functions = array_map(fn($f) => $f->getName(), $ext->getFunctions());
        $this->assertContains('daily_usage', $functions);
    }

    public function testUnlimitedPlanReturnsUnlimitedFlag(): void
    {
        $plan = new Plan();
        $plan->setUsageLimit(null);

        $user = new User();
        $user->setPlan($plan);

        $result = $this->makeExtension(0)->getDailyUsage($user);

        $this->assertTrue($result['unlimited']);
        $this->assertEquals(0, $result['used']);
        $this->assertNull($result['limit']);
        $this->assertEquals(0, $result['percentage']);
    }

    public function testLimitedPlanCalculatesCorrectly(): void
    {
        $plan = new Plan();
        $plan->setUsageLimit(10);

        $user = new User();
        $user->setPlan($plan);

        $result = $this->makeExtension(5)->getDailyUsage($user);

        $this->assertFalse($result['unlimited']);
        $this->assertEquals(5, $result['used']);
        $this->assertEquals(10, $result['limit']);
        $this->assertEquals(50, $result['percentage']);
    }

    public function testPercentageCapsAt100(): void
    {
        $plan = new Plan();
        $plan->setUsageLimit(2);

        $user = new User();
        $user->setPlan($plan);

        $result = $this->makeExtension(10)->getDailyUsage($user);

        $this->assertEquals(100, $result['percentage']);
    }

    public function testZeroUsageGivesZeroPercent(): void
    {
        $plan = new Plan();
        $plan->setUsageLimit(20);

        $user = new User();
        $user->setPlan($plan);

        $result = $this->makeExtension(0)->getDailyUsage($user);

        $this->assertEquals(0, $result['percentage']);
        $this->assertEquals(0, $result['used']);
    }
}
