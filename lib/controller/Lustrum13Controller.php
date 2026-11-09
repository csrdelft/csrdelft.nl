<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Lustrum13Controller extends AbstractController
{
    /**
     * @Auth(P_LOGGED_IN)
     */
    #[Route(path: '/panorama')]
    public function lustrum(): Response
    {
        return $this->render('lustrum13/index.html.twig');
    }
}
