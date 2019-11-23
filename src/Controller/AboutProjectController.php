<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\SourceRepository;

class AboutProjectController extends AbstractController
{
    /**
     * @Route("/apie_projekta", name="about_project")
     */
    public function index(SourceRepository $sourceRepository)
    {
        return $this->render('front/pages/about/index.html.twig', [
            'sources' => $sourceRepository->findAll(),
        ]);
    }
}
