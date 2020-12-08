<?php


namespace App\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use App\Entity\Review;

/**
 * @IsGranted("ROLE_ADMIN")
 * Class ArticleAdminController
 * @package App\Controller
 */
class ArticleAdminController extends AbstractController
{
    /**
     * @Route ("/article/admin" , name="admin_article_list")
     * @Method ({"GET"})
     * @return mixed
     */
    public function index() {

        /*
         * This whole method will find all the article objects. It is then used to render the objects as a table on the homepage
         */

        // return new Response('<h1>Hello<h1>');
        $articles=$this->getDoctrine()->getRepository
        (Review::class)->findAll();


        return $this->render('admin/adminIndex.html.twig', array('articles' => $articles)); //we use this to generate article objects, each containing all of our article data
    }
}