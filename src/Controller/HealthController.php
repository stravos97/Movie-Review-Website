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
        $retries = 3;
        $delay = 1; // seconds
        
        for ($i = 0; $i < $retries; $i++) {
            try {
                $connection->executeQuery('SELECT 1')->fetchOne();
                $dbOk = true;
                break;
            } catch (\Throwable $e) {
                $dbOk = false;
                if ($i < $retries - 1) {
                    sleep($delay);
                    $delay *= 2; // exponential backoff
                }
            }
        }

        return $this->json([
            'status' => 'ok',
            'db' => $dbOk ? 'up' : 'down',
        ]);
    }
}

