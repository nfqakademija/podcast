<?php

namespace App\Service;

use App\Repository\PodcastRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class ListenLaterService
{
    private $podcastRepository;
    private $userRepository;
    private $entityManager;
    private $security;

    public function __construct(
        PodcastRepository $podcastRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        Security $security
    ) {
        $this->podcastRepository = $podcastRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    public function manage(int $podcast_id, int $user_id, string $action): void
    {
        $podcast = $this->podcastRepository->findPodcastById($podcast_id);
        $podcast = $podcast[0];

        $user = $this->userRepository->findOneBy(['id' => $user_id]);

        if ($action == 'add') {
            $user->addPodcast($podcast);
        } elseif ($action == 'remove') {
            $user->removePodcast($podcast);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function getPodcasts(): ArrayCollection
    {
        $user = $this->security->getUser();

        if ($user != null) {
            $podcasts = $user->getPodcasts();
            $arrayOfIds = $podcasts->map(function ($param) {
                return $param->getId();
            });
        } else {
            $arrayOfIds = [];
        }

        return $arrayOfIds;
    }
}
