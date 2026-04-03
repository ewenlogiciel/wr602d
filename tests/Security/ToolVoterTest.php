<?php

namespace App\Tests\Security;

use App\Entity\Plan;
use App\Entity\Tool;
use App\Entity\User;
use App\Security\ToolVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class ToolVoterTest extends TestCase
{
    private function makeVoter(array $reachableRoles): ToolVoter
    {
        $hierarchy = $this->createMock(RoleHierarchyInterface::class);
        $hierarchy->method('getReachableRoleNames')->willReturn($reachableRoles);
        return new ToolVoter($hierarchy);
    }

    private function makeToken(User $user): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        $token->method('getRoleNames')->willReturn($user->getRoles());
        return $token;
    }

    public function testSupportsToolAccessAttribute(): void
    {
        $voter = $this->makeVoter([]);
        $tool  = new Tool();

        $this->assertTrue(
            (new \ReflectionMethod($voter, 'supports'))->invoke($voter, ToolVoter::ACCESS, $tool)
        );
    }

    public function testDoesNotSupportOtherAttributes(): void
    {
        $voter = $this->makeVoter([]);
        $tool  = new Tool();

        $this->assertFalse(
            (new \ReflectionMethod($voter, 'supports'))->invoke($voter, 'OTHER_ATTR', $tool)
        );
    }

    public function testGrantsAccessWhenUserHasMatchingPlanRole(): void
    {
        $plan = new Plan();
        $plan->setRole('ROLE_BASIC');

        $tool = new Tool();
        $tool->addPlan($plan);

        $user = new User();
        $user->setIsVerified(true);
        $user->setPlan($plan);

        $voter = $this->makeVoter(['ROLE_USER', 'ROLE_BASIC']);
        $token = $this->makeToken($user);

        $result = $voter->vote($token, $tool, [ToolVoter::ACCESS]);
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testDeniesAccessWhenUserLacksPlanRole(): void
    {
        $premiumPlan = new Plan();
        $premiumPlan->setRole('ROLE_PREMIUM');

        $tool = new Tool();
        $tool->addPlan($premiumPlan);

        $basicPlan = new Plan();
        $basicPlan->setRole('ROLE_BASIC');

        $user = new User();
        $user->setPlan($basicPlan);

        $voter = $this->makeVoter(['ROLE_USER', 'ROLE_BASIC']);
        $token = $this->makeToken($user);

        $result = $voter->vote($token, $tool, [ToolVoter::ACCESS]);
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testDeniesAccessForUnauthenticatedUser(): void
    {
        $plan = new Plan();
        $plan->setRole('ROLE_FREE');

        $tool = new Tool();
        $tool->addPlan($plan);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(null);
        $token->method('getRoleNames')->willReturn([]);

        $voter = $this->makeVoter([]);
        $result = $voter->vote($token, $tool, [ToolVoter::ACCESS]);
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }
}
