<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use App\Service\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param GuardAuthenticatorHandler $guardHandler
     * @param LoginFormAuthenticator $authenticator
     * @param TokenGenerator $tokenGenerator
     * @return RedirectResponse|Response|null
     */
    public function register(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $authenticator,
        TokenGenerator $tokenGenerator
    ) {
        if ($this->getUser()) {
            return $this->redirectToRoute('user_panel');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setConfirmationToken($tokenGenerator->getRandomSecureToken(100));

            $this->entityManager->persist($user);
            $this->entityManager->flush();

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
     * @param User $user
     * @return Response|NotFoundHttpException
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

        return $this->createNotFoundException();
    }
}
