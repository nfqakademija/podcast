<?php

namespace App\Controller;

use App\Entity\Podcast;
use App\Entity\User;
use App\Form\UpdateProfileType;
use App\Repository\TagRepository;
use App\Service\MailService;
use App\Service\TaggingService;
use App\Service\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ListenLaterService;
use App\Service\LikePodcastService;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @IsGranted("ROLE_USER")
 */
class UsersController extends AbstractController
{
    /**
     * @var MailService
     */
    private $mailService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ListenLaterService
     */
    private $listenLaterService;

    /**
     * @var TokenGenerator
     */
    private $tokenGenerator;

    /**
     * @var LikePodcastService
     */
    private $likePodcast;

    public function __construct(
        MailService $mailService,
        EntityManagerInterface $entityManager,
        ListenLaterService $listenLaterService,
        TokenGenerator $tokenGenerator,
        LikePodcastService $likePodcast
    ) {
        $this->mailService = $mailService;
        $this->entityManager = $entityManager;
        $this->listenLaterService = $listenLaterService;
        $this->tokenGenerator = $tokenGenerator;
        $this->likePodcast = $likePodcast;
    }

    /**
     * @Route("/vartotojo_panele", name="user_panel")
     * @param Request $request
     * @param TagRepository $tagRepository
     * @param TaggingService $taggingService
     * @return RedirectResponse|Response
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
            'podcastsLater' => $this->listenLaterService->getPodcasts(),
            'likedPodcastsIds' => $this->likePodcast->getLikedPodcasts()
        ]);
    }

    /**
     * @Route("atnaujinti_vartotoja", name="update_user_credentials")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return RedirectResponse|Response
     */
    public function updateUserProfile(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        /** @var User $user */
        $user = $this->getUser();
        $oldEmail = $user->getUsername();
        $form = $this->createForm(UpdateProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('plainPassword')->getData()) {
                $user->setPassword($passwordEncoder->encodePassword($user, $form->get('plainPassword')->getData()));
            }
            if ($oldEmail !== $user->getUsername()) {
                $user->setIsConfirmed(false);
                $user->setConfirmationToken($this->tokenGenerator->getRandomSecureToken(100));
                $this->mailService->sendVerification($user);
                $this->addFlash('info', 'Jums išsiūstas el. pašto patvirtinimas');
            }

            $this->entityManager->flush();
            $this->addFlash('success', 'Vartotojo duomenys atnaujinti');

            return $this->redirectToRoute('update_user_credentials');
        }

        return $this->render('front/pages/users/user_profile_update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("ijungti_pranesimus", name="enable_mailing_by_tags")
     * @return RedirectResponse
     */
    public function enableMailingByUserTags()
    {
        /** @var User $user */
        $user = $this->getUser();
        $user->setIsSubscriber(true);
        $this->entityManager->flush();

        $this->addFlash('success', 'Nuo šiol galite gauti naujienlaiškius pagal tagus.');

        return $this->redirectToRoute('user_panel');
    }

    /**
     * @Route("isjungti_pranesimus", name="disable_mailing_by_tags")
     * @return RedirectResponse
     */
    public function disableMailingByUserTags()
    {
        /** @var User $user */
        $user = $this->getUser();
        $user->setIsSubscriber(false);
        $this->entityManager->flush();

        $this->addFlash('success', 'Naujienlaiškių siuntimas jums išjungtas.');

        return $this->redirectToRoute('user_panel');
    }

    /**
     * @Route("/listen_later/{podcast}", name="listen_later",  methods={"POST"})
     * @param Request $request
     * @param Podcast $podcast
     * @return Response
     */
    public function addPodcastToListenLaterPlaylist(Request $request, Podcast $podcast)
    {
        $action = $request->get('action');

        $this->listenLaterService->manage($podcast, $action);

        return new Response();
    }

    /**
     * @Route("/like_podcast/{podcast}", name="like_podcast",  methods={"POST"})
     * @param Request $request
     * @param Podcast $podcast
     * @return Response
     */
    public function manageLikeOnPodcast(Request $request, Podcast $podcast)
    {
        $this->likePodcast->manage($podcast);

        return new Response();
    }
}
