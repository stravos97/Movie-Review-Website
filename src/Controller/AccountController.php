<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted ("ROLE_USER")
 * Class AccountController
 * @package App\Controller
 */
class AccountController extends AbstractController
{
    /**
     * @Route("/account", name="article_account")
     */
    public function index(): Response
    {
        return $this->render('account/index.html.twig', [

        ]);
    }
}
