<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;
    private $userRepository;
    private $passwordEncoder;
    private $urlGenerator;
    private $csrfTokenManager;

    public function __construct(UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
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
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token')
        ];
    }

    /**
     * After getCredentials is run, the array from that method is passed here to fidn that email on the server
     * @param $credentials
     * @param UserProviderInterface $userProvider
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
       $token = new CsrfToken('authenticate', $credentials['csrf_token'] ?? '');
       if (!$this->csrfTokenManager->isTokenValid($token)) {
           throw new InvalidCsrfTokenException('Invalid CSRF token.');
       }
       return $this->userRepository->findOneBy(['email' => $credentials['email']]);

    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate('website_login');
    }

    /**
     * If the previous steps successfully passed, the password is checked to see if it is correct
     * @param $credentials
     * @param UserInterface $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $user = $token->getUser();
        $name = method_exists($user, 'getFirstName') && $user->getFirstName() ? $user->getFirstName() : (method_exists($user, 'getEmail') ? $user->getEmail() : '');
        if ($request->hasSession()) {
            $request->getSession()->getFlashBag()->add('success', sprintf('Welcome back%s%s!', $name ? ', ' : '', $name));
            // Redirect to originally requested URL if available
            if ($target = $this->getTargetPath($request->getSession(), $providerKey)) {
                return new RedirectResponse($target);
            }
        }
        return new RedirectResponse($this->urlGenerator->generate('article_list'));
    }

}
