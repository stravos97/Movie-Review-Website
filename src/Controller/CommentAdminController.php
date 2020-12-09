<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentAdminController extends AbstractController
{
    /**
     * Request is used to get POST or GET data
     * @Route("/admin/comment", name="comment_admin")
     */
    public function index(CommentRepository $repository, Request $request): Response
    {
      //  $query = $request->query->get('q'); //GET request //required code


        /**
         * Not proper fix, not sure why get request in symfony is not working. Tried using Controller instead if AbstractController
         */
        $q=null;
        if (!empty($_GET["q"])) {
            $q = $_GET["q"];
        }


        $comments = $repository->findAllWithSearch($q);

       // $comments = $repository->findAllWithSearch('q'); //required code

        //$comments = $repository->findBy([]);
        return $this->render('comment_admin/index.html.twig', [
            'comments' => $comments
        ]);
    }
}
