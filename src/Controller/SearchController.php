<?php

namespace App\Controller;

use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    /**
     * @Route("/search", name="search_reviews", methods={"GET"})
     */
    public function search(Request $request, ReviewRepository $reviews): Response
    {
        $q = trim((string) $request->query->get('q', ''));
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $results = [];
        $mode = 'none';

        if ($q !== '') {
            try {
                $results = $reviews->searchFullText($q, $limit, $offset);
                $mode = 'fulltext';
            } catch (\Throwable $e) {
                // Fallback to LIKE search if fulltext fails
                $results = $reviews->search($q, $limit, $offset);
                $mode = 'partial';
            }
        }

        return $this->render('search/results.html.twig', [
            'query' => $q,
            'results' => $results,
            'mode' => $mode,
        ]);
    }
}

