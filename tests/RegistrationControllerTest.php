<?php

namespace App\Tests;

use App\Repository\PlanRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testRegisterPageIsAccessible(): void
    {
        $this->client->request('GET', '/register');

        self::assertResponseIsSuccessful();
        self::assertPageTitleContains('Inscription');
    }

    public function testRegisterPageContainsForm(): void
    {
        $this->client->request('GET', '/register');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('input[name="registration_form[email]"]');
        self::assertSelectorExists('input[name="registration_form[plainPassword]"]');
        self::assertSelectorExists('input[name="registration_form[firstname]"]');
        self::assertSelectorExists('input[name="registration_form[lastname]"]');
    }
}
