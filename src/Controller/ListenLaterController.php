<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

        return $this->render('listen_later/add.html.twig');
    }

     /**
     * @Route("/listen_later/add", name="listen_later_pod")
     */
    public function getPodcasts(Request $request)
    {
        $podcast_id = $request->get('podcast_id');
        $user_id = 1;
        $action = $request->get('action');
        $this->listenLaterService->manage($podcast_id, $user_id, $action);

        return $this->render('listen_later/add.html.twig');
    }
}
