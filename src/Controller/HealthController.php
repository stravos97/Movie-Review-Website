<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class HealthController extends AbstractController
{
    /**
     * @Route("/health", name="app_health", methods={"GET"})
     */
    public function health(Connection $connection): JsonResponse
    {
        $dbOk = true;
        try {
            $connection->executeQuery('SELECT 1')->fetchOne();
        } catch (\Throwable $e) {
            $dbOk = false;
        }

        return $this->json([
            'status' => 'ok',
            'db' => $dbOk ? 'up' : 'down',
        ]);
    }
}

