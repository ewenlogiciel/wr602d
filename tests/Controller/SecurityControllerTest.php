<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('input[name="email"]');
        $this->assertSelectorExists('input[name="password"]');
        $this->assertSelectorExists('button[type="submit"]');
    }

    public function testLoginWithInvalidCredentialsShowsError(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $client->submitForm('Se connecter', [
            'email'    => 'inexistant@example.com',
            'password' => 'mauvais_mdp',
        ]);

        $this->assertResponseRedirects('/login');
        $client->followRedirect();
        $this->assertSelectorExists('body');
    }

    public function testLogoutRedirects(): void
    {
        $client = static::createClient();
        $client->request('GET', '/logout');

        $this->assertResponseRedirects();
    }
}
