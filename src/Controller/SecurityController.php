<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use App\Service\MailService;
use App\Service\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class SecurityController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/prisijungimas", name="app_login")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('user_panel');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('front/pages/users/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/atsijungimas", name="app_logout")
     */
    public function logout()
    {
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }

    /**
     * @Route("/reset-password/{passwordResetToken}", name="reset_password")
     * @param User $user
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return RedirectResponse|Response|NotFoundHttpException
     */
    public function resetUserPassword(
        User $user,
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        if ($user) {
            $form = $this->createForm(ResetPasswordType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                $user->setPasswordResetToken(null);
                $this->entityManager->flush();

                $this->addFlash('success', 'Slaptažodis sėkmingai pakeistas, galite prisijungti!');

                return $this->redirectToRoute('podcasts');
            }

            return $this->render('front/pages/users/reset_password.html.twig', [
                'form' => $form->createView()
            ]);
        }

        return $this->createNotFoundException();
    }

    /**
     * @Route("slaptazodzio-atkurimas", name="recover_password", methods={"GET", "POST"})
     * @param UserRepository $userRepository
     * @param Request $request
     * @param TokenGenerator $tokenGenerator
     * @param MailService $mailService
     * @return RedirectResponse|Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function sendResetPasswordEmail(
        UserRepository $userRepository,
        Request $request,
        TokenGenerator $tokenGenerator,
        MailService $mailService
    ) {
        $submittedToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('reset_password', $submittedToken)) {
            $email = $request->request->get('username');
            $user = $userRepository->findOneBy(['username' => $email]);

            if ($user) {
                $user->setPasswordResetToken($tokenGenerator->getRandomSecureToken(200));
                $this->entityManager->flush();
                $mailService->sendPasswordResetEmail($user);
                $this->addFlash('success', 'Slaptažodžio atkūrimas pradėtas, patikrinkite el. paštą');

                return $this->redirectToRoute('app_login');
            }
            $this->addFlash('danger', 'Toks vartotojas neegzistuoja!');

            return $this->redirectToRoute('recover_password');
        }

        return $this->render('front/pages/users/request_reset_password.html.twig');
    }
}
