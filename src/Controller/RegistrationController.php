<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use App\Service\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class RegistrationController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/registracija", name="app_register")
     */
    public function register(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $authenticator,
        TokenGenerator $tokenGenerator
    ) {
        if ($this->getUser()) {
            return $this->redirectToRoute('podcasts');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setConfirmationToken($tokenGenerator->getRandomSecureToken(100));

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // do anything else you need here, like send an email

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }

        return $this->render('front/pages/users/register.html.twig', [
            'registrationForm' => $form->createView(),
            'title' => 'Susikurkite paskyrą'
        ]);
    }

    /**
     * @Route("patvirtinimas/{confirmationToken}", name="confirm_user")
     */
    public function confirmUser(User $user)
    {
        if ($user) {
            $user->setIsConfirmed(true);
            $user->setConfirmationToken(null);

            $this->entityManager->flush();

            return $this->render('emails/confirm_email.html.twig', [
                'user' => $user
            ]);
        }

        $this->createNotFoundException();
    }
}
