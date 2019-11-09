<?php

namespace App\Controller;

use App\Entity\Podcast;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('home/index.html.twig', [
            'someVariable' => 'Krepšinio podcastai',
        ]);
    }

    /**
     * @Route("/posts", name="posts")
     */
    public function front()
    {
        $podcasts = $this->getDoctrine()
            ->getRepository(Podcast::class)
            ->findAll();
        //dd($podcasts);

        return $this->render('front/pages/posts/index.html.twig', [
            'podcasts' => $podcasts,
        ]);
    }
}
