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
        // Symfony 4.4: access the container via self::$container
        $container = self::$container;
        // Obtain repository via Doctrine to avoid relying on service visibility
        /** @var \Doctrine\Persistence\ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        /** @var ReviewRepository $repo */
        $repo = $doctrine->getRepository(\App\Entity\Review::class);
        $results = $repo->recent(5);
        $this->assertIsArray($results);
    }
}
