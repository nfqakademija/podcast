<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_USER")
 */
class UsersController extends AbstractController
{
    /**
     * @Route("/vartotojo_panele", name="user_panel")
     */
    public function panel()
    {
        return $this->render('front/pages/users/panel.html.twig');
    }
}
