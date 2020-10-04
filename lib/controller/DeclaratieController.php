<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeclaratieController extends AbstractController {
	/**
	 * @return Response
	 * @Route("/declaratie/nieuw", name="declaratie_nieuw", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function nieuw() {
		return $this->render('declaratie/nieuw.html.twig');
	}
}
