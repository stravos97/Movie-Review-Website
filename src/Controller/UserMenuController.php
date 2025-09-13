<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserMenuController extends AbstractController
{
    /**
     * @Route("/_menu/user", name="user_menu", methods={"GET"})
     */
    public function menu(ReviewRepository $reviews, CommentRepository $comments): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return new Response('', 204); // No Content
        }
        $uid = method_exists($user, 'getId') ? $user->getId() : null;
        if (!$uid) {
            return new Response('', 204);
        }

        $myReviews = $reviews->byUser($uid, 5, 0);
        $myComments = $comments->recentByUser($uid, 5, 0);

        return $this->render('includes/_user_menu.html.twig', [
            'myReviews' => $myReviews,
            'myComments' => $myComments,
        ]);
    }
}

