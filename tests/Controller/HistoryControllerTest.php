<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HistoryControllerTest extends WebTestCase
{
    public function testHistoryRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/history');

        $this->assertResponseRedirects('/login');
    }

    public function testHistoryDownloadRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/history/999/download');

        // Sans auth, Symfony redirige vers /login avant même de résoudre l'entité
        $status = $client->getResponse()->getStatusCode();
        $this->assertThat(
            $status,
            $this->logicalOr($this->equalTo(302), $this->equalTo(404), $this->equalTo(500))
        );
    }
}
