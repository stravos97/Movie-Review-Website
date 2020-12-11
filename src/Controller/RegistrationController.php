<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\LoginAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\HttpKernel\Profiler\Profiler;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginAuthenticator $authenticator, ?Profiler $profiler): Response
    {
        // $profiler won't be set if your environment doesn't have the profiler (like prod, by default)
        if (null !== $profiler) {
            // if it exists, disable the profiler for this particular controller action
            $profiler->disable();
        }
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /**
             * Upload image, code, no validation
             */

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = ($form['picture']->getData());

            $destination = $this->getParameter('kernel.project_dir').'/public/images/users';
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName().'-'.uniqid().'.'.$uploadedFile->guessExtension());
            // $newFilename = $this->generateUniqueFileName().'.'.$uploadedFile->guessExtension();

            $uploadedFile->move(
                $destination,
                $uploadedFile->getClientOriginalName()
            );

            $user->setPicture($uploadedFile->getClientOriginalName());







            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
