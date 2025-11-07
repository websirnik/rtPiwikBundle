<?php

namespace rtPiwikBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertStringContainsString('Hello World', $client->getResponse()->getContent());
    }
}
