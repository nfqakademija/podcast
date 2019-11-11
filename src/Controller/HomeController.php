<?php

namespace App\Controller;

use App\Entity\Podcast;
use App\Repository\SourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface; 

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
     * @Route("/posts", name="podcasts")
     */
    public function front(Request $request, PaginatorInterface $paginator, SourceRepository $sourceRepository)
    {
        $allPodcasts = $this->getDoctrine()
            ->getRepository(Podcast::class)
            ->findAll();
      
        $sources = 
       // Paginate the results of the query
       $podcasts = $paginator->paginate(
            // Doctrine Query, not results
            $allPodcasts,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            10
        );
         //dd(knp_pagination_render(podcasts)) ;
        return $this->render('front/pages/posts/index.html.twig', [
            'podcasts' => $podcasts,
            'sources' => $sourceRepository->findAll()
        ]);
    }
}
