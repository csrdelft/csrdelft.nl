<?php


namespace CsrDelft\controller;


use CsrDelft\common\Annotation\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArroController extends AbstractController
{

    /**
     * @return Response
     * @Route("/arro")
     * @Auth(P_LOGGED_IN)
     */
    public function arro()
    {
        return $this->render('arro/index.html.twig');
    }


}
