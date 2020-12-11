<?php


namespace App\Controller;


use App\Entity\Comment;
use App\Entity\Review;
use App\Form\NewArticle;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleAdminController extends AbstractController
{

    /**
     * @IsGranted("ROLE_USER")
     * @Route ("/article/new", name="new_article")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return RedirectResponse|Response
     * @throws \Exception
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
     * @param Request $request
     * @param $id
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
     * @Method({"GET", "POST"})
     * @param Request $request
     * @param $id
     * @return RedirectResponse|Response
     */
    public function edit(Request $request, $id, Review $review) {

        /*
         * This part finds the article by the ID passed in
         */

        /**
         * Allows us to deny  access to something if we don't own it and if we are not an admin
         */

        if ($review->getUserID() != $this->getUser() && !$this->isGranted('ROLE_ADMIN_ARTICLE')){
            throw $this->createAccessDeniedException('No Access');
        }

        /**
         * Acts as a voter to manage whether we can get access, mange is a permission attribute
         */
//        if (!$this->isGranted('EDIT', $review)){
//            throw $this->createAccessDeniedException('No Access');
//        }

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
     * @param $id
     * @param Request $request
     * @param EntityManagerInterface $entityManager
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