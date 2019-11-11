<?php

namespace App\Controller;

use App\Repository\PodcastRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="posts")
     */
    public function front(PodcastRepository $podcastRepository)
    {
        return $this->render('front/pages/posts/index.html.twig', [
            'podcasts' => $podcastRepository->findAll(),
        ]);
    }
}
