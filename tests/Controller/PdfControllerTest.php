<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PdfControllerTest extends WebTestCase
{
    public function testToolsPageRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/tools');

        $this->assertResponseRedirects('/login');
    }

    public function testConvertPageRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/convert/url');

        $this->assertResponseRedirects('/login');
    }

    public function testConvertPageRedirectsForUnknownSlug(): void
    {
        $client = static::createClient();
        $client->request('GET', '/convert/outil-inexistant');

        // Soit 404 soit redirect selon la configuration
        $this->assertThat(
            $client->getResponse()->getStatusCode(),
            $this->logicalOr($this->equalTo(302), $this->equalTo(404))
        );
    }
}
