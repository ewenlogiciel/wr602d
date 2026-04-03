<?php

namespace App\Tests\Entity;

use App\Entity\Generation;
use App\Entity\Tool;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class GenerationTest extends TestCase
{
    public function testGettersSetters(): void
    {
        $user = new User();
        $tool = new Tool();
        $date = new \DateTimeImmutable('2024-01-01');

        $generation = new Generation();
        $generation->setUser($user);
        $generation->setTool($tool);
        $generation->setSource('https://example.com');
        $generation->setFile('gen_abc123.pdf');
        $generation->setCreatedAt($date);

        $this->assertSame($user, $generation->getUser());
        $this->assertSame($tool, $generation->getTool());
        $this->assertEquals('https://example.com', $generation->getSource());
        $this->assertEquals('gen_abc123.pdf', $generation->getFile());
        $this->assertEquals($date, $generation->getCreatedAt());
    }
}
