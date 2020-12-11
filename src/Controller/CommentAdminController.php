<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 * @IsGranted("ROLE_ADMIN")
 * Class CommentAdminController
 * @package App\Controller
 */
class CommentAdminController extends BaseController
{
    /**
     * Request is used to get POST or GET data.
     * @Route("/admin/comment", name="comment_admin")
     */
    public function index(CommentRepository $repository, Request $request, PaginatorInterface $paginator, ?Profiler $profiler): Response
    {

        // $profiler won't be set if your environment doesn't have the profiler (like prod, by default)
        if (null !== $profiler) {
            // if it exists, disable the profiler for this particular controller action
            $profiler->disable();
        }

    //$this->denyAccessUnlessGranted('ROLE_ADMIN'); //another way to deny access for a simple method


        /**
         * Method gets the query from the URL,
         * then sends it to the comment repository where an inner join is performed so you can search and filter by search
         */

        $query = $request->query->get('q'); //GET request //required code




        //$comments = $repository->findAllWithSearch($q);

        $queryBuilder = $repository->getWithSearch($query);

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

