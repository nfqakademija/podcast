<?php

namespace App\Service;

use App\Repository\PodcastRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\Podcast;

class LikePodcastService
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

    public function manage(Podcast $podcast): void
    {
        $user = $this->security->getUser();

        $user->toogleLike($podcast);

        $this->entityManager->flush();
    }

    /**
     * @return iterable
     */
    public function getLikedPodcasts(): iterable
    {
        $user = $this->security->getUser();

        if ($user != null) {
            $podcasts = $user->getLikedPodcasts();
            $arrayOfIds = $podcasts->map(function ($param) {
                return $param->getId();
            });
        } else {
            $arrayOfIds = [];
        }

        return $arrayOfIds;
    }
}
