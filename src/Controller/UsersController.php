<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UpdateProfileType;
use App\Repository\TagRepository;
use App\Service\MailService;
use App\Service\TaggingService;
use App\Service\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ListenLaterService;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @IsGranted("ROLE_USER")
 */
class UsersController extends AbstractController
{
    private $mailService;
    private $entityManager;
    private $listenLaterService;
    private $tokenGenerator;

    public function __construct(
        MailService $mailService,
        EntityManagerInterface $entityManager,
        ListenLaterService $listenLaterService,
        TokenGenerator $tokenGenerator
    ) {
        $this->mailService = $mailService;
        $this->entityManager = $entityManager;
        $this->listenLaterService = $listenLaterService;
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * @Route("/vartotojo_panele", name="user_panel")
     */
    public function showUserPanel(Request $request, TagRepository $tagRepository, TaggingService $taggingService)
    {
        /** @var User $user */
        $user = $this->getUser();
        $listenLaterPodcasts = $user->getPodcasts();
        $likedPodcasts = $user->getLikedPodcasts();
        $token = $request->request->get('token');
        $userTags = $tagRepository->findTagsByUser($user);

        if ($this->isCsrfTokenValid('add_tags', $token)) {
            $submittedTags = $request->request->get('tags');
            $taggingService->handleUserTags($submittedTags, $userTags);
            $this->addFlash('success', 'Nauji parametrai išsaugoti!');

            return $this->redirectToRoute('user_panel');
        }

        return $this->render('front/pages/users/panel.html.twig', [
            'tags' => $userTags,
            'listenLaterPodcasts' => $listenLaterPodcasts,
            'likedPodcasts' => $likedPodcasts,
            'podcastsLater' => $this->listenLaterService->getPodcasts()
        ]);
    }

    /**
     * @Route("atnaujinti_vartotoja", name="update_user_credentials")
     */
    public function updateUserProfile(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        /** @var User $user */
        $user = $this->getUser();
        $oldEmail = $user->getEmail();
        $form = $this->createForm(UpdateProfileType::class, $user, [
            'action' => $this->generateUrl('update_user_credentials')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('plainPassword')->getData()) {
                $user->setPassword($passwordEncoder->encodePassword($user, $form->get('plainPassword')->getData()));
            }
            if ($oldEmail !== $user->getEmail()) {
                $user->setIsConfirmed(false);
                $user->setConfirmationToken($this->tokenGenerator->getRandomSecureToken(100));
                $this->mailService->sendVerification($user);
                $this->addFlash('info', 'Atnaujinus el. paštą būtinas patvirtinimas');
            }

            $this->entityManager->flush();
            $this->addFlash('success', 'Vartotojo duomenys atnaujinti');

            return $this->redirectToRoute('user_panel');
        }

        return $this->render('front/pages/users/_panel_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("ijungti_pranesimus", name="enable_mailing_by_tags")
     */
    public function enableMailingByUserTags()
    {
        /** @var User $user */
        $user = $this->getUser();
        $user->setIsSubscriber(true);
        $this->entityManager->flush();

        $this->addFlash('success', 'Nuo šiol galite susikurti suasmenintus naujienlaiškius');

        return $this->redirectToRoute('user_panel');
    }

    /**
     * @Route("isjungti_pranesimus", name="disable_mailing_by_tags")
     */
    public function disableMailingByUserTags()
    {
        /** @var User $user */
        $user = $this->getUser();
        $user->setIsSubscriber(false);
        $this->entityManager->flush();

        $this->addFlash('success', 'Naujienlaiškių siuntimas jums išjungtas');

        return $this->redirectToRoute('user_panel');
    }

    /**
     * @Route("daily-newsletter-by-tags", name="newsletter_by_tags")
     */
    public function sendNewsletterOfDailyPodcastsByTags()
    {
        $this->mailService->sendDailyNewsletterBySelectedTagsToRegisteredUsers();
    }
}
