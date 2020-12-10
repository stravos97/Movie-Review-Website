<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 *
 * Class AccountController
 * @package App\Controller
 */
class AccountController extends /**AbstractController**/ BaseController
{
    /**
     * @IsGranted ("ROLE_USER")
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

    /**
     * @IsGranted ("ROLE_SUPER_ADMIN_DISPLAY_USER")
     * @Route("/admin/displayUsers", name="deleteUser")
     */
    public function displayAllUsers(UserRepository $repository, Request $request, PaginatorInterface $paginator): Response
    {


        $query = $request->query->get('q');



        //$reviews = $repository->findAllWithSearch($q);

        $queryBuilder = $repository->getWithSearch($query);

        /**
         * We aren't responsible for executing the query, we are only responsible for building a query and passing it to the paginator
         */
        $pagination = $paginator->paginate(
            $queryBuilder, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        // $comments = $repository->findAllWithSearch('q'); //required code

        //$comments = $repository->findBy([]);
        return $this->render('admin/displayUsers.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * @IsGranted ("ROLE_SUPER_ADMIN_DELETEUSER")
     *  @Route("/remove/${id}", name="display_All_Users")
     * @Method({"DELETE"})
     */
    public function deleteUser(Request $request, $id)
    {

        $user = $this->getDoctrine()->getRepository(User::class)->findBy($id);
        //var_dump($user);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        $response = new Response();
        $response->send();

    }



}
