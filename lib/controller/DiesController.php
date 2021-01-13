<?php


namespace CsrDelft\controller;


use CsrDelft\common\Annotation\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DiesController extends AbstractController
{

	/**
	 * Shadows dies stek 2021
	 *
	 * @return Response
	 * @Route("/gateway-of-india")
	 * @Auth(P_LOGGED_IN)
	 */
	public function dies() {
		return $this->render('dies/index.html.twig');
	}
}
