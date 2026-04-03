<?php

namespace App\Tests;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ResetPasswordControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = static::getContainer();
        $this->em = $container->get('doctrine')->getManager();
        $this->userRepository = $container->get(UserRepository::class);
    }

    public function testResetPasswordPageIsAccessible(): void
    {
        $this->client->request('GET', '/reset-password');

        self::assertResponseIsSuccessful();
    }

    public function testResetPasswordWithUnknownEmailDoesNotCrash(): void
    {
        $this->client->request('GET', '/reset-password');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Envoyer', [
            'reset_password_request_form[email]' => 'inconnu@example.com',
        ]);

        // Redirige vers check-email même si l'email n'existe pas (sécurité : pas de leak)
        self::assertResponseRedirects('/reset-password/check-email');
    }
}
