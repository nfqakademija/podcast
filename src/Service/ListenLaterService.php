<?php

namespace App\Service;

use App\Repository\PodcastRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\Podcast;

class ListenLaterService
{
    /**
     * @var PodcastRepository
     */
    private $podcastRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /** @var Security  */
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

    /**
     * @param Podcast $podcast
     * @param string $action
     */
    public function manage(Podcast $podcast, string $action): void
    {
        $user = $this->security->getUser();

        if ($action == 'add') {
            $user->addPodcast($podcast);
        } elseif ($action == 'remove') {
            $user->removePodcast($podcast);
        }

        $this->entityManager->flush();
    }

    /**
     * @return iterable
     */
    public function getPodcasts(): iterable
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
