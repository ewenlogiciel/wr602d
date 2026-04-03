<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class UserCheckerTest extends TestCase
{
    private UserChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new UserChecker();
    }

    public function testCheckPreAuthThrowsForUnverifiedUser(): void
    {
        $user = new User();
        $user->setIsVerified(false);

        $this->expectException(CustomUserMessageAccountStatusException::class);
        $this->expectExceptionMessage('Votre adresse e-mail n\'a pas encore été vérifiée.');

        $this->checker->checkPreAuth($user);
    }

    public function testCheckPreAuthPassesForVerifiedUser(): void
    {
        $user = new User();
        $user->setIsVerified(true);

        $this->checker->checkPreAuth($user);
        $this->addToAssertionCount(1);
    }

    public function testCheckPreAuthIgnoresNonAppUser(): void
    {
        $user = $this->createMock(\Symfony\Component\Security\Core\User\UserInterface::class);

        $this->checker->checkPreAuth($user);
        $this->addToAssertionCount(1);
    }

    public function testCheckPostAuthDoesNothing(): void
    {
        $user = new User();
        $user->setIsVerified(false);

        $this->checker->checkPostAuth($user);
        $this->addToAssertionCount(1);
    }
}
