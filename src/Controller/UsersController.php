<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UsersController extends AbstractController
{
    /**
     * @Route("/registracija", name="user_registration")
     */
    public function register()
    {
        return $this->render('front/pages/users/register.html.twig');
    }

    /**
     * @Route("/vartotojo_panele", name="user_panel")
     */
    public function panel()
    {
        return $this->render('front/pages/users/panel.html.twig');
    }
}
