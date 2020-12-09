<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentAdminController extends AbstractController
{
    /**
     * Request is used to get POST or GET data.
     * @Route("/admin/comment", name="comment_admin")
     */
    public function index(CommentRepository $repository, Request $request, PaginatorInterface $paginator): Response
    {




        /**
         * Method gets the query from the URL,
         * then sends it to the comment repository where an inner join is performed so you can search and filter by search
         */

       // $query = $request->query->get('q'); //GET request //required code

        /**
         * Not proper fix, not sure why get request in symfony is not working. Tried using Controller instead if AbstractController
         */
        $q=null;
        if (!empty($_GET["q"])) {
            $q = $_GET["q"];
        }


        //$comments = $repository->findAllWithSearch($q);

        $queryBuilder = $repository->getWithSearch($q);

        /**
         * We aren't responsible for executing the query, we are only responsible for building a query and passing it to the paginator
         */
        $pagination = $paginator->paginate(
            $queryBuilder, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            5/*limit per page*/
        );

        // $comments = $repository->findAllWithSearch('q'); //required code

        //$comments = $repository->findBy([]);
        return $this->render('comment_admin/index.html.twig', [
            'pagination' => $pagination
        ]);
    }
}

