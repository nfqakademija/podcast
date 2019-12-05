<?php

namespace App\Controller;

use App\Entity\Podcast;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class LikeController extends AbstractController
{

    /**
     * @Route("/like/{podcast}", name="like")
     * @IsGranted("ROLE_USER")
     *
     * @param Podcast $podcast
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function toogleLike(Podcast $podcast, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();
        $user->toogleLike($podcast);

        $entityManager->persist($podcast);
        $entityManager->flush();

        $isLike = $user->isLike($podcast);
        $countLikes = count($podcast->getLikedPodcasts());

        return $this->json(['likes' => $isLike, 'countLikes' => $countLikes]);
    }
}
