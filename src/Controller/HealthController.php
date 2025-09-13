<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class HealthController extends AbstractController
{
    /**
     * @Route("/health", name="app_health", methods={"GET"})
     */
    public function health(ManagerRegistry $doctrine): JsonResponse
    {
        $dbOk = true;
        try {
            $connection = $doctrine->getConnection();
            // If connection cannot be established, mark DB down but return 200
            if ($connection && ($connection->isConnected() || $connection->connect())) {
                try {
                    if (method_exists($connection, 'executeQuery')) {
                        $res = $connection->executeQuery('SELECT 1');
                        if ($res && method_exists($res, 'fetchOne')) {
                            $res->fetchOne();
                        }
                    } else {
                        $connection->query('SELECT 1');
                    }
                } catch (\Throwable $dbError) {
                    $dbOk = false;
                }
            } else {
                $dbOk = false;
            }
        } catch (\Throwable $e) {
            $dbOk = false;
        }

        return $this->json([
            'status' => 'ok',
            'db' => $dbOk ? 'up' : 'down',
        ]);
    }
}
