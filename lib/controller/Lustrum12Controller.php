<?php


namespace CsrDelft\controller;


use CsrDelft\common\Annotation\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Lustrum12Controller extends AbstractController
{

	/**
	 * Shadows dies stek 2021
	 *
	 * @return Response
	 * @Route("/tijdloos")
	 * @Auth(P_LOGGED_IN)
	 */
	public function lustrum() {
		return $this->render('lustrum12/index.html.twig');
	}

//	/**
//	 * Ketzers voor dies stek 2021
//	 *
//	 * @return Response
//	 * @Route("/gateway-of-india/ketzers")
//	 * @Auth(P_LOGGED_IN)
//	 */
//	public function ketzers() {
//		return $this->render('dies/ketzers.html.twig');
//	}
//
//	/**
//	 * Livestream voor dies stek 2021
//	 *
//	 * @return Response
//	 * @Route("/gateway-of-india/livestream")
//	 * @Auth(P_LOGGED_IN)
//	 */
//	public function livestream() {
//		return $this->render('dies/livestream.html.twig');
//	}
}
