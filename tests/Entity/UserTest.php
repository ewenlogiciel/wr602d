<?php

namespace App\Tests\Entity;

use App\Entity\Plan;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testRolesAlwaysContainRoleUser(): void
    {
        $user = new User();
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testRolesIncludePlanRole(): void
    {
        $plan = new Plan();
        $plan->setRole('ROLE_BASIC');

        $user = new User();
        $user->setPlan($plan);

        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertContains('ROLE_BASIC', $user->getRoles());
    }

    public function testRolesAreUnique(): void
    {
        $plan = new Plan();
        $plan->setRole('ROLE_USER');

        $user = new User();
        $user->setPlan($plan);

        $roles = $user->getRoles();
        $this->assertEquals(count($roles), count(array_unique($roles)));
    }

    public function testRolesWithoutPlan(): void
    {
        $user = new User();
        $roles = $user->getRoles();
        $this->assertContains('ROLE_USER', $roles);
    }

    public function testGettersSetters(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setFirstname('Jean');
        $user->setLastname('Dupont');
        $user->setPhone('+33600000000');
        $user->setIsVerified(true);

        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('test@example.com', $user->getUserIdentifier());
        $this->assertEquals('Jean', $user->getFirstname());
        $this->assertEquals('Dupont', $user->getLastname());
        $this->assertEquals('+33600000000', $user->getPhone());
        $this->assertTrue($user->isVerified());
    }

    public function testIsVerifiedDefaultsFalse(): void
    {
        $user = new User();
        $this->assertFalse($user->isVerified());
    }
}
