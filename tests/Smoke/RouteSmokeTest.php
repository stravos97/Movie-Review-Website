<?php

namespace App\Tests\Smoke;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RouteSmokeTest extends WebTestCase
{
    public function testHomePageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertTrue($client->getResponse()->isSuccessful(), 'Homepage should return 2xx');
    }

    public function testSearchPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/search?q=test');
        $this->assertTrue($client->getResponse()->isSuccessful(), 'Search page should return 2xx');
    }
}

