<?php

namespace App\Security;

use App\Entity\Tool;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class ToolVoter extends Voter
{
    public const ACCESS = 'TOOL_ACCESS';

    public function __construct(private readonly RoleHierarchyInterface $roleHierarchy) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::ACCESS && $subject instanceof Tool;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (!$token->getUser() instanceof User) {
            return false;
        }

        $reachableRoles = $this->roleHierarchy->getReachableRoleNames($token->getRoleNames());

        /** @var Tool $subject */
        foreach ($subject->getPlan() as $plan) {
            if (in_array($plan->getRole(), $reachableRoles, true)) {
                return true;
            }
        }

        return false;
    }
}
