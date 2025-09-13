<?php

namespace App\Tests\Repository;

use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ReviewRepositoryTest extends KernelTestCase
{
    public function testRecentReturnsArray(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        /** @var ReviewRepository $repo */
        $repo = $container->get(ReviewRepository::class);
        $results = $repo->recent(5);
        $this->assertIsArray($results);
    }
}

