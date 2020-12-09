<?php


namespace App\Controller;


use App\Repository\ReviewRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function index(ReviewRepository $repository, Request $request) {

        $q=null;
        if (!empty($_GET["q"])) {
            $q = $_GET["q"];
        }


        $reviews = $repository->findAllWithSearch($q);

        // $comments = $repository->findAllWithSearch('q'); //required code

        //$comments = $repository->findBy([]);
        return $this->render('admin/adminIndex.html.twig', [
            'articles' => $reviews
        ]);
    }
}