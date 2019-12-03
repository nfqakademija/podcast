<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\MailService;
use App\Service\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UsersController extends AbstractController
{
    /**
     * @Route("/vartotojo_panele", name="user_panel")
     * @IsGranted("ROLE_USER")
     */
    public function panel()
    {
        return $this->render('front/pages/users/panel.html.twig');
    }

    /**
     * @Route("slaptazodzio-atkurimas", name="recover_password", methods={"GET", "POST"})
     */
    public function sendResetPasswordEmail(
        UserRepository $userRepository,
        MailService $mailService,
        EntityManagerInterface $entityManager,
        TokenGenerator $tokenGenerator,
        Request $request
    ) {
        $submittedToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('reset_password', $submittedToken)) {
            $email = $request->request->get('username');
            $user = $userRepository->findOneBy(['username' => $email]);

            if ($user) {
                $user->setPasswordResetToken($tokenGenerator->getRandomSecureToken(200));
                $entityManager->flush();
                $mailService->sendPasswordResetEmail($user);
                $this->addFlash('success', 'Slaptažodžio atkūrimas pradėtas, patikrinkite el. paštą');

                return $this->redirectToRoute('app_login');
            } else {
                $this->addFlash('danger', 'Toks vartotojas neegzistuoja!');

                return $this->redirectToRoute('recover_password');
            }
        }

        return $this->render('front/pages/users/request_reset_password.html.twig');
    }
}
