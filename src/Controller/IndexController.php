<?php
namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Review;
use App\Entity\User;
use App\Form\NewArticle;
use App\Repository\CommentRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



/*
 * Dont forget comments start with /*
 * ROUTES have to start with /** . If you don't do this you will get an unexpected error. Method won't ever be reached, therefore won't be run
 */

class IndexController extends BaseController { //article controller
    /**
     * @Route("/" , name="article_list")
     * @Method({"GET"})
     */
    public function index(ReviewRepository $repository, Request $request, PaginatorInterface $paginator): Response
    {

        /*
         * This whole method will find all the article objects. It is then used to render the objects as a table on the homepage
         */

        $q = $request->query->get('q');

        $queryBuilder = $repository->getWithSearch($q);

        /**
         * We aren't responsible for executing the query, we are only responsible for building a query and passing it to the paginator
         */
        $pagination = $paginator->paginate(
            $queryBuilder, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            6/*limit per page*/
        );




        return $this->render('articles/index.html.twig', [
            'pagination' => $pagination
        ]);

    }


    /**
     * @Route("/article/{id}", name="article_show")
     */ //
    public function show($id){ //gets the id from the {} above

        //$comments = $commentRepository->findBy(['movieID' => $review]); //manual way of retrieving comments

        // $comments = $review->getComments();

//        foreach ($comments as $comment){
//            dd($comment);
//        }
        //dd($comments);

        /*
         * This method is rendered when the user clicks the show button on the homepage. This will get the id number from the url (given by index() Objects above).
         * This will then find the article with that id number and render the other fields that id number holds as an array. This array then displays the title and body in this case
         *  This method should always be last (at the bottom of the page and the last method run). As it will try to load anything after /article as an id, even if it is not an id e.g.title
         */
        $article = $this->getDoctrine()->getRepository(Review::class)->find($id);

        return $this->render('articles/show.html.twig', array('article' => $article)); //only contains a single article
    }

}