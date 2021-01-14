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

	/**
	 * Ketzers voor dies stek 2021
	 *
	 * @return Response
	 * @Route("/gateway-of-india/ketzers")
	 * @Auth(P_LOGGED_IN)
	 */
	public function ketzers() {
		return $this->render('dies/ketzers.html.twig');
	}

	/**
	 * Livestream voor dies stek 2021
	 *
	 * @return Response
	 * @Route("/gateway-of-india/livestream")
	 * @Auth(P_LOGGED_IN)
	 */
	public function livestream() {
		return $this->render('dies/livestream.html.twig');
	}
}
