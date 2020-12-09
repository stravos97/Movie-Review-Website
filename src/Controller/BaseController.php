<?php


namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Not a real controller, just a helpful base class.
 * Made to allow account controller to recognize user objects
 * Method allows us to get our user (:User ) and return the parent (the User class)
 *
 * If we want to do something frequently, but it doesn't make sense to move that login in to a service, we should do it here. (add a new protected function here)
 *
 * Class BaseController
 * @package App\Controller
 */
abstract class BaseController extends AbstractController
{
    protected function getUser(): User
    {
        return parent::getUser();
    }

    protected function getUserForArticle(): User
    {
        return parent::getUser();
    }

}