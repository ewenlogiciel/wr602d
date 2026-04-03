<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    public function testHomepageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
    }

    public function testHomepageContainsPlans(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('FREE', $crawler->filter('body')->text());
        $this->assertStringContainsString('BASIC', $crawler->filter('body')->text());
        $this->assertStringContainsString('PREMIUM', $crawler->filter('body')->text());
    }
}
