<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;
    private $userRepository;
    private $router;
    private $passwordEncoder;

    /**
     * routerInterface is used for redirection. Instead of hardcoding a redirect we use RouterInterface
     * LoginAuthenticator constructor.
     * @param UserRepository $userRepository
     * @param RouterInterface $router
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(UserRepository $userRepository, RouterInterface $router, UserPasswordEncoderInterface $passwordEncoder){
        
        $this->userRepository = $userRepository;
        $this->router = $router;
        $this->passwordEncoder = $passwordEncoder;
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
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password')
        ];

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );
        return $credentials;
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

    /**
     * What should happen if the login is uncessful e.g. wrong username
     */
    protected function getLoginUrl()
    {
    return $this->router->generate('website_login');
    }

    /**
     * If the previous steps successfully passed, the password is checked to see if it is correct
     * @param $credentials
     * @param UserInterface $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
       // return true;
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    /**
     * What should happen oncce login is successful and the user is authenticated
     * @param Request $request
     * @param TokenInterface $token
     * @param $providerKey
     * @return RedirectResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        /**
         * We are assigning the target path and then checking to see if it is empty or not
         * If it is not empty and there is someting stored in the session then, return that target path, otherwise go to the homepage
         * e.g. if we try to access a protected link that requires authentication, run this
         */
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)){
            return new RedirectResponse($targetPath);
        }
       return new RedirectResponse($this->router->generate('article_list'));
    }

}
