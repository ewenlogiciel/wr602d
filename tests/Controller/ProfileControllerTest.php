<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileControllerTest extends WebTestCase
{
    public function testProfileRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/profile');

        $this->assertResponseRedirects('/login');
    }

    public function testProfilePasswordChangeRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('POST', '/profile/password');

        $this->assertResponseRedirects('/login');
    }
}
