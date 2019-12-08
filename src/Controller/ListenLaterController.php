<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ListenLaterService;

class ListenLaterController extends AbstractController
{
    protected $listenLaterService;

    public function __construct(ListenLaterService $listenLaterService)
    {
        $this->listenLaterService = $listenLaterService;
    }

    /**
     * @Route("/listen_later/", name="listen_later",  methods={"POST"})
     */
    public function manageAction(Request $request)
    {
        $podcast_id = $request->get('podcast_id');
        $user_id = $request->get('user_id');
        $action = $request->get('action');

        $this->listenLaterService->manage($podcast_id, $user_id, $action);

        return new Response();
    }
}
