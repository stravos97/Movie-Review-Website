<?php


namespace App\Controller;


use App\Repository\ReviewRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use App\Entity\Review;

/**
 * @IsGranted("ROLE_ADMIN")
 * Class ReviewAdminController
 * @package App\Controller
 */
class ReviewAdminController extends AbstractController
{
    /**
     * @Route ("/admin/article" , name="admin_article_list")
     * @Method ({"GET"})
     * @return mixed
     */
    public function index(ReviewRepository $repository, Request $request, PaginatorInterface $paginator): Response
    {

        $q=null;
        if (!empty($_GET["q"])) {
            $q = $_GET["q"];
        }




       //$reviews = $repository->findAllWithSearch($q);

        $queryBuilder = $repository->getWithSearch($q);

        /**
         * We aren't responsible for executing the query, we are only responsible for building a query and passing it to the paginator
         */
        $pagination = $paginator->paginate(
            $queryBuilder, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            2/*limit per page*/
        );

        // $comments = $repository->findAllWithSearch('q'); //required code

        //$comments = $repository->findBy([]);
        return $this->render('admin/adminIndex.html.twig', [
            'pagination' => $pagination
        ]);
    }
}