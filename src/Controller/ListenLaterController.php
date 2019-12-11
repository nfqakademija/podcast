<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ListenLaterService;
use App\Entity\Podcast;

class ListenLaterController extends AbstractController
{
    protected $listenLaterService;

    public function __construct(ListenLaterService $listenLaterService)
    {
        $this->listenLaterService = $listenLaterService;
    }

    /**
     * @Route("/listen_later/{podcast}", name="listen_later",  methods={"POST"})
     */
    public function manageAction(Request $request, Podcast $podcast)
    {
        $action = $request->get('action');

        $this->listenLaterService->manage($podcast, $action);

        return new Response();
    }
}
