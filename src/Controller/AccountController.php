<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted ("ROLE_USER")
 * Class AccountController
 * @package App\Controller
 */
class AccountController extends /**AbstractController**/ BaseController
{
    /**
     * To log the email address of who is logged in, we use logger
     * @Route("/account", name="article_account")
     */
    public function index(LoggerInterface $logger): Response
    {
        $logger->debug('Checking account page for' .$this->getUser()->getEmail());
       // dd($this->getUser());

        return $this->render('account/index.html.twig', [

        ]);
    }
}
