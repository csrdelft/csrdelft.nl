<?php

namespace CsrDelft\controller;

use Symfony\Component\Routing\Attribute\Route;
use CsrDelft\common\Annotation\Auth;
use Symfony\Component\HttpFoundation\Response;

class Lustrum12Controller extends AbstractController
{
	/**
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/tijdloos')]
	public function lustrum()
	{
		return $this->render('lustrum12/index.html.twig');
	}

	/**
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/tijdloos/thema')]
	public function LustrumThema()
	{
		return $this->render('lustrum12/thema.html.twig');
	}

	/**
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/tijdloos/opening')]
	public function lustrumOpening()
	{
		return $this->render('lustrum12/opening.html.twig');
	}

	/**
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/tijdloos/lustrumweek')]
	public function lustrumWeek()
	{
		return $this->render('lustrum12/lustrumweek.html.twig');
	}

	/**
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/tijdloos/terugnaardetoekomst')]
	public function lustrumWeek2()
	{
		return $this->render('lustrum12/lustrumweek2.html.twig');
	}

	/**
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/tijdloos/zingmee')]
	public function lustrumActiviteitCantus()
	{
		return $this->render('lustrum12/zingmee.html.twig');
	}

	/**
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/tijdloos/dikkemerch')]
	public function lustrumMerch()
	{
		return $this->render('lustrum12/dikkemerch.html.twig');
	}

	/**
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/tijdloos/diesthema')]
	public function diesThema()
	{
		return $this->render('lustrum12/diesthema.html.twig');
	}

	/**
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/tijdloos/dies')]
	public function dies()
	{
		return $this->render('lustrum12/dies.html.twig');
	}

	/**
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/tijdloos/dies/gala')]
	public function diesgala()
	{
		return $this->render('lustrum12/diesgala.html.twig');
	}

	/**
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/tijdloos/dies/etiquette')]
	public function etiquette()
	{
		return $this->render('lustrum12/etiquette.html.twig');
	}

	/**
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/tijdloos/lustrumreis/inschrijven')]
	public function lustrumreisinschrijven()
	{
		return $this->render('lustrum12/lustrumreisinschrijven.html.twig');
	}

	/**
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/tijdloos/lustrumreis')]
	public function lustrumreis()
	{
		return $this->render('lustrum12/lustrumreis.html.twig');
	}

	/**
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/tijdloos/slotactiviteit')]
	public function hoogtijd()
	{
		return $this->render('lustrum12/slotactiviteit.html.twig');
	}

	/**
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/tijdloos/gala')]
	public function lustrumgala()
	{
		return $this->render('lustrum12/lustrumgala.html.twig');
	}
}
