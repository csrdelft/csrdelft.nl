<?php


namespace CsrDelft\controller;


use CsrDelft\common\Annotation\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Lustrum12Controller extends AbstractController
{

	/**
	 * @return Response
	 * @Route("/tijdloos")
	 * @Auth(P_LOGGED_IN)
	 */
	public function lustrum() {
		return $this->render('lustrum12/index.html.twig');
	}

	/**
	 * @return Response
	 * @Route("/tijdloos/thema")
	 * @Auth(P_LOGGED_IN)
	 */
	public function LustrumThema() {
		return $this->render('lustrum12/thema.html.twig');
	}

	/**
	 * @return Response
	 * @Route("/tijdloos/opening")
	 * @Auth(P_LOGGED_IN)
	 */
	public function lustrumOpening() {
		return $this->render('lustrum12/opening.html.twig');
	}

	/**
	 * @return Response
	 * @Route("/tijdloos/lustrumweek")
	 * @Auth(P_LOGGED_IN)
	 */
	public function lustrumWeek() {
		return $this->render('lustrum12/lustrumweek.html.twig');
	}

	/**
	 * @return Response
	 * @Route("/tijdloos/zingmee")
	 * @Auth(P_LOGGED_IN)
	 */
	public function lustrumActiviteitCantus() {
		return $this->render('lustrum12/cantus.html.twig');
	}
}
