<?php

namespace App\Tests\Unit\Controller;

use Symfony\Component\Panther\PantherTestCase;

class BaseTest extends PantherTestCase
{
    public function testSomething(): void
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/');

        $this->assertSelectorTextContains('h1', 'Hello World');
    }
}
