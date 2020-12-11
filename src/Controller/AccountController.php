<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\User;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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


        $comments = $this->getDoctrine()->getRepository(Comment::class)->findBy(array('userID' => $this->getUser()->getId()));
        $picture = $this->getUser()->getPicture();
       // dd($comments);
        $sizeOfDeleted = 0;

        if ($comments){
            foreach ($comments as $size){
                if ($size->getIsDeleted()){
                    $sizeOfDeleted++;

                }
            }
        }


        return $this->render('account/index.html.twig', [
            'userInfo' => $comments,
            'picture' => $picture,
            'sizeOfDeleted' => $sizeOfDeleted
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
     * @Route("userss/remove/{id}", methods={"DELETE"})
     */
    public function delete(Request $request, $id)
    {
       // var_dump($id);

        $user = $this->getDoctrine()->getRepository(User::class)->findBy($id);
        //var_dump($user);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        $response = new Response();
        $response->send();

    }



}
