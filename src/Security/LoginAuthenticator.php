<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;

class LoginAuthenticator extends AbstractFormLoginAuthenticator
{
    private $userRepository;
    
    public function __construct(UserRepository $userRepository){
        
        $this->userRepository = $userRepository;
    }
    public function supports(Request $request)
    {
        //die('test');
        return $request->attributes->get('_route') === 'website_login' //this checks to see if the URL is /login. i.e am I currently on the login page?
            && $request->isMethod('post'); //if this first line is ignored, the following methods are ignored, and the request goes on like normal
    }

    /**
     * If we return true from support, the following method is run
     * @param Request $request
     */
    public function getCredentials(Request $request)
    {
        //dd($request->request->all()); //we use this for all post methods
        return [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password')
        ];
    }

    /**
     * After getCredentials is run, the array from that method is passed here to fidn that email on the server
     * @param $credentials
     * @param UserProviderInterface $userProvider
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
       return $this->userRepository->findOneBy(['email' => $credentials['email']]);

    }

    
    protected function getLoginUrl()
    {
        // TODO: Implement getLoginUrl() method.
    }

    /**
     * If the previous steps successfully passed, the password is checked to see if it is correct
     * @param $credentials
     * @param UserInterface $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // todo
    }

}
