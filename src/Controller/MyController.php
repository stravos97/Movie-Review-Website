<?php

namespace App\Controller;

use App\Repository\ReviewRepository;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyController extends AbstractController
{
    /**
     * @Route("/me/reviews", name="my_reviews", methods={"GET"})
     */
    public function myReviews(Request $request, ReviewRepository $reviews): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('website_login');
        }
        $uid = method_exists($user, 'getId') ? (int) $user->getId() : null;
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Fetch one extra to detect next page
        $itemsPlusOne = $reviews->byUser($uid, $limit + 1, $offset);
        $hasNext = \count($itemsPlusOne) > $limit;
        $items = \array_slice($itemsPlusOne, 0, $limit);

        return $this->render('me/reviews.html.twig', [
            'items' => $items,
            'page' => $page,
            'has_next' => $hasNext,
        ]);
    }

    /**
     * @Route("/me/comments", name="my_comments", methods={"GET"})
     */
    public function myComments(Request $request, CommentRepository $comments): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('website_login');
        }
        $uid = method_exists($user, 'getId') ? (int) $user->getId() : null;
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Fetch one extra to detect next page
        $itemsPlusOne = $comments->recentByUser($uid, $limit + 1, $offset);
        $hasNext = \count($itemsPlusOne) > $limit;
        $items = \array_slice($itemsPlusOne, 0, $limit);

        return $this->render('me/comments.html.twig', [
            'items' => $items,
            'page' => $page,
            'has_next' => $hasNext,
        ]);
    }
}
