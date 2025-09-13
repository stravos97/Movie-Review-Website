<?php

namespace App\Tests\Repository;

use App\Kernel;
use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ReviewRepositoryTest extends KernelTestCase
{
    protected static function createKernel(array $options = [])
    {
        return new Kernel('test', true);
    }

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
