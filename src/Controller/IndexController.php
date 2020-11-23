<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndexController extends AbstractController { //article controller
    /**
     * @Route("/article/save")
     * @Method({"GET"})
     */ #instead of using routes.yaml and specificying the route there, we can do this
    public function index() {
        // return new Response('<h1>Hello<h1>');

        $articles = ['Article 1', 'Article 2'];
         return $this->render('articles/index.html.twig', array('articles' => $articles)); //we use the array to pass stuff into the controller
    }

}