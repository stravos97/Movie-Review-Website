<?php
namespace App\Controller;


use App\Entity\Review;
use App\Repository\ReviewRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Profiler\Profiler;



/*
 * Dont forget comments start with /*
 * ROUTES have to start with /** . If you don't do this you will get an unexpected error. Method won't ever be reached, therefore won't be run
 */

class IndexController extends BaseController { //article controller

    /**
     * @Route("/" , name="article_list")
     * @Method({"GET"})
     */
    public function index(ReviewRepository $repository, Request $request, PaginatorInterface $paginator, ?Profiler $profiler): Response
    {

        // $profiler won't be set if your environment doesn't have the profiler (like prod, by default)
        if (null !== $profiler) {
            // if it exists, disable the profiler for this particular controller action
            $profiler->disable();
        }


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
    public function show($id, ?Profiler $profiler){ //gets the id from the {} above

        // $profiler won't be set if your environment doesn't have the profiler (like prod, by default)
        if (null !== $profiler) {
            // if it exists, disable the profiler for this particular controller action
            $profiler->disable();
        }

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