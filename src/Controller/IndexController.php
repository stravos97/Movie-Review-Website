<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Article;

class IndexController extends AbstractController { //article controller
    /**
     * @Route("/article/save" , name="article_list")
     * @Method({"GET"})
     */ #instead of using routes.yaml and specificying the route there, we can do this
    public function index() {
        // return new Response('<h1>Hello<h1>');
    $articles=$this->getDoctrine()->getRepository
    (Article::class)->findAll();


         return $this->render('articles/index.html.twig', array('articles' => $articles)); //we use this to generate article objects, each containing all of our article data
    }

    /**
     * @Route("/article/{id}", name="article_show")
     */
    public function show($id){ //gets the id from the {} above
        $article = $this->getDoctrine()->getRepository(Article::class)->find($id);

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