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
     * @IsGranted("ROLE_USER")
     * @Route ("/article/new", name="new_article")
     * @Method({"GET", "POST"})
     * @param Request $request
     *
     */
    public function new(Request $request, EntityManagerInterface $entityManager){

        /*
         * This entire method will create a form, when clicking the 'new article' button.
         * It will render it, then allow the user to submit a completed form once the requirements are met
         */

        $review = new Review();
        $startTime = new \DateTime('@'.strtotime('now'));
        $review->setUserID($this->getUser());

        $form = $this->createForm(
            NewArticle::class,
            $review
        ); // YOu don't need to get the data ->getData. You pass the whole form in
    //dd($form);
        /**
         * Checks to see if the form is submitted and sends the completed form to the database
         */
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($review);
            $entityManager->flush();
            return $this->redirectToRoute('article_list');
        }

        return $this->render('articles/new.html.twig', [
            'form' => $form->createView(),
        ]);


    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/article/delete/{id}")
     * @Method({"DELETE"})
     */
    public function delete(Request $request, $id) {
        $article = $this->getDoctrine()->getRepository(Review::class)->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($article);
        $entityManager->flush();

        $response = new Response();
        $response->send();


    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/article/edit/{id}", name="edit_article")
     * Method({"GET", "POST"})
     */
    public function edit(Request $request, $id) {

       /*
        * This part finds the article by the ID passed in
        */
        //$article = new Review();

        $review = new Review();
        $review = $this->getDoctrine()->getRepository(Review::class)->find($id);

        //this article will be found and passed in to the form
        $form = $this->createForm(NewArticle::class, $review); // YOu don't need to get the data ->getData. You pass the whole form in


        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('article_list');
        }

        return $this->render('articles/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }



    /**
     * Request is used to get the form data
     *
     * @Route("/article/{id}", name="article_submit_comment", methods={"POST"})
     *
     * @return Response
     */
    public function submitComment($id, Request $request, EntityManagerInterface $entityManager)
    {

//    dd($commentBody);
//        dd();
//
//  //  dd($request->request->all());
//
//        $userID = $request->request->get('currentUser');
//        $isDeleted = 0;
        // dd($id);
//
//
        $movieID = $this->getDoctrine()->getRepository(Review::class)->find($id);
        // dd($movieID);
//        $datee = $movieID->getDate();
        $commentBody = $request->request->get('commentData');
        $userId = $this->getUser();
        $comment = new Comment($movieID, $commentBody, $userId, false);
//        dd($comment);

//        dd($movieID);
//       $comment->setCommentBody($commentBody);
//       $comment->setDate(new \DateTimeImmutable());
//        $comment->setIsDeleted($isDeleted);
//        $comment->setTestMovieID($movieID->getId());
//        $comment->setTestUserID($userID);
//        //dd($comment);

        $form = $this->createFormBuilder($comment);
//dd($form);
//        /**
//         * Checks to see if the form is submitted and sends the completed form to the database
//         */

        if ($form) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();
            //return $this->redirect($url, 301);
            return $this->redirect($request->getUri());
        }
        return $this->render('articles/show.html.twig');
//        return $this->render('../articles/show.html.twig');


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




//    /**
//     * @Route ("/article/saves")
//     */ //Save data directly to the database
//    public function save(){
//        $entityManager = $this->getDoctrine()->getManager(); //EntityManager interface is used to allow applications to manage and search for entities in the relational database.
//        $article =new Article();
//        $article->setTitle('Article 2');
//        $article->setBody('This is the body for article 2');
//
//        $entityManager->persist($article); //persist the data (tells the system we want to eventually save the data)
//        $entityManager->flush(); //to execute the code we use the flush command
//
//        return new Response('Saves an article with the id of ' . $article->getID());
//
//    }

}