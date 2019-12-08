<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use App\Service\MailService;
use App\Service\TaggingService;
use App\Service\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ListenLaterService;

class UsersController extends AbstractController
{
    private $mailService;
    private $entityManager;
    private $listenLaterService;

    public function __construct(
        MailService $mailService,
        EntityManagerInterface $entityManager,
        ListenLaterService $listenLaterService
    ) {
        $this->mailService = $mailService;
        $this->entityManager = $entityManager;
        $this->listenLaterService = $listenLaterService;
    }

    /**
     * @Route("/vartotojo_panele", name="user_panel")
     * @IsGranted("ROLE_USER")
     */
    public function showUserPanel(Request $request, TagRepository $tagRepository, TaggingService $taggingService)
    {
        /** @var User $user */
        $user = $this->getUser();
        $listenLaterPodcasts = $user->getPodcasts();
        $likedPodcasts = $user->getLikedPodcasts();

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        $userTags = $tagRepository->findTagsByUser($user);
        $token = $request->request->get('token');

        if ($this->isCsrfTokenValid('add_tags', $token)) {
            $submittedTags = $request->request->get('tags');
            $taggingService->handleUserTags($submittedTags, $userTags);
            $this->addFlash('success', 'Nauji parametrai išsaugoti!');

            return $this->redirectToRoute('user_panel');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Vartotojo duomenys atnaujinti');

            return $this->redirectToRoute('user_panel');
        }

        return $this->render('front/pages/users/panel.html.twig', [
            'form' => $form->createView(),
            'tags' => $userTags,
            'listenLaterPodcasts' => $listenLaterPodcasts,
            'likedPodcasts' => $likedPodcasts,
            'podcastsLater' => $this->listenLaterService->getPodcasts()
        ]);
    }

    /**
     * @Route("slaptazodzio-atkurimas", name="recover_password", methods={"GET", "POST"})
     */
    public function sendResetPasswordEmail(
        UserRepository $userRepository,
        TokenGenerator $tokenGenerator,
        Request $request
    ) {
        $submittedToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('reset_password', $submittedToken)) {
            $email = $request->request->get('username');
            $user = $userRepository->findOneBy(['username' => $email]);

            if ($user) {
                $user->setPasswordResetToken($tokenGenerator->getRandomSecureToken(200));
                $this->entityManager->flush();
                $this->mailService->sendPasswordResetEmail($user);
                $this->addFlash('success', 'Slaptažodžio atkūrimas pradėtas, patikrinkite el. paštą');

                return $this->redirectToRoute('app_login');
            }
            $this->addFlash('danger', 'Toks vartotojas neegzistuoja!');

            return $this->redirectToRoute('recover_password');
        }

        return $this->render('front/pages/users/request_reset_password.html.twig');
    }

    /**
     * @Route("daily-newsletter-by-tags", name="newsletter_by_tags")
     */
    public function sendNewsletterOfDailyPodcastsByTags()
    {
        $this->mailService->sendDailyNewsletterBySelectedTagsToRegisteredUsers();
    }
}
