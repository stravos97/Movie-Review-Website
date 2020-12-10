<?php
namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Review;
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

class IndexController extends AbstractController { //article controller
    /**
     * @Route("/" , name="article_list")
     * @Method({"GET"})
     */
    public function index(ReviewRepository $repository, Request $request, PaginatorInterface $paginator): Response
    {

        /*
         * This whole method will find all the article objects. It is then used to render the objects as a table on the homepage
         */

        $q=null;
        if (!empty($_GET["q"])) {
            $q = $_GET["q"];
        }

        $queryBuilder = $repository->getWithSearch($q);

        /**
         * We aren't responsible for executing the query, we are only responsible for building a query and passing it to the paginator
         */
        $pagination = $paginator->paginate(
            $queryBuilder, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            2/*limit per page*/
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
    public function new(Request $request){

        /*
         * This entire method will create a form, when clicking the 'new article' button.
         * It will render it, then allow the user to submit a completed form once the requirements are met
         */
        $review = new Review();
        $form = $this->createForm(NewArticle::class, $review); // YOu don't need to get the data ->getData. You pass the whole form in

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

        //$article = new Article();
//        /*
//         * Creates a form with the title and body fields. This form is used to create a new Article
//         * The title is required and the body is not;
//         */
//        $form = $this->createFormBuilder($article) //creating the form here with all the attributes and classes
//            ->add('title', TextType::class, array('attr' => array('class' => 'form-control')))
//            ->add('body', TextareaType::class, array(
//                'required' => true,
//                'attr' => array('class' => 'form-control')
//            ))
//            ->add('save', SubmitType::class, array(
//                'label' => 'Create',
//                'attr' => array('class' => 'btn btn-primary mt-3')
//            ))
//            ->getForm();
//
//        /**
//         * Checks to see if the form is submitted and sends the completed form to the database
//         */
//        $form->handleRequest($request);
//        if ($form->isSubmitted() && $form->isValid()) {
//            $article = $form->getData();
//
//            $entityManager = $this->getDoctrine()->getManager();
//            $entityManager->persist($article);
//            $entityManager->flush();
//
//            return $this->redirectToRoute('article_list');
//        }
//
//        /*
//         * Sends a GET request to the twig page, and shows the form based on the attributes set above
//         */
//        return $this->render('articles/new.html.twig', array(
//            'form' => $form->createView() //we are passing form->crateView as form
//        ));

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

    /**
     * Request is used to get the form data
     *
     * @Route("/article/{id}/addComment", name="article_submit_comment", methods={"POST"})
     *
     * @return Response
     */
    public function submitComment($id, Request $request, Review $review, EntityManagerInterface $entityManager): Request
    {

//    $commentBody = $request->request->get('commentData');
//
//
//
//
//  //  dd($request->request->all());
//
//
//        $userID = $request->request->get('currentUser');
//        $isDeleted = 0;
//
//
//
//        $comment = new Comment();
//        dd($comment);
        //$movieID = $this->getDoctrine()->getRepository(Review::class)->find($id);

//       $comment->setCommentBody($commentBody);
//       $comment->setDate(new \DateTimeImmutable());
//        $comment->setIsDeleted($isDeleted);
//        $comment->setTestMovieID($movieID->getId());
//        $comment->setTestUserID($userID);
//        //dd($comment);

//        $form = $this->createForm(Comment::class, $movieID);
//
//        /**
//         * Checks to see if the form is submitted and sends the completed form to the database
//         */
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//
//            $entityManager = $this->getDoctrine()->getManager();
//            $entityManager->persist($comment);
//            $entityManager->flush();
//
//            return $this->redirectToRoute('article_list');
//        }


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