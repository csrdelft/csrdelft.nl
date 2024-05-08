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
	public function lustrum(): Response
	{
		return $this->render('lustrum12/index.html.twig');
	}

	/**
	 * @return Response
	 * @Route("/tijdloos/thema")
	 * @Auth(P_LOGGED_IN)
	 */
	public function LustrumThema(): Response
	{
		return $this->render('lustrum12/thema.html.twig');
	}

	/**
	 * @return Response
	 * @Route("/tijdloos/opening")
	 * @Auth(P_LOGGED_IN)
	 */
	public function lustrumOpening(): Response
	{
		return $this->render('lustrum12/opening.html.twig');
	}

	/**
	 * @return Response
	 * @Route("/tijdloos/lustrumweek")
	 * @Auth(P_LOGGED_IN)
	 */
	public function lustrumWeek(): Response
	{
		return $this->render('lustrum12/lustrumweek.html.twig');
	}

	/**
	 * @return Response
	 * @Route("/tijdloos/terugnaardetoekomst")
	 * @Auth(P_LOGGED_IN)
	 */
	public function lustrumWeek2(): Response
	{
		return $this->render('lustrum12/lustrumweek2.html.twig');
	}

	/**
	 * @return Response
	 * @Route("/tijdloos/zingmee")
	 * @Auth(P_LOGGED_IN)
	 */
	public function lustrumActiviteitCantus(): Response
	{
		return $this->render('lustrum12/zingmee.html.twig');
	}

	/**
	 * @return Response
	 * @Route("/tijdloos/dikkemerch")
	 * @Auth(P_LOGGED_IN)
	 */
	public function lustrumMerch(): Response
	{
		return $this->render('lustrum12/dikkemerch.html.twig');
	}

	/**
	 * @return Response
	 * @Route("/tijdloos/diesthema")
	 * @Auth(P_LOGGED_IN)
	 */
	public function diesThema(): Response
	{
		return $this->render('lustrum12/diesthema.html.twig');
	}

	/**
	 * @return Response
	 * @Route("/tijdloos/dies")
	 * @Auth(P_LOGGED_IN)
	 */
	public function dies(): Response
	{
		return $this->render('lustrum12/dies.html.twig');
	}

	/**
	 * @return Response
	 * @Route("/tijdloos/dies/gala")
	 * @Auth(P_LOGGED_IN)
	 */
	public function diesgala(): Response
	{
		return $this->render('lustrum12/diesgala.html.twig');
	}

	/**
	 * @return Response
	 * @Route("/tijdloos/dies/etiquette")
	 * @Auth(P_LOGGED_IN)
	 */
	public function etiquette(): Response
	{
		return $this->render('lustrum12/etiquette.html.twig');
	}

	/**
	 * @return Response
	 * @Route("/tijdloos/lustrumreis/inschrijven")
	 * @Auth(P_LOGGED_IN)
	 */
	public function lustrumreisinschrijven(): Response
	{
		return $this->render('lustrum12/lustrumreisinschrijven.html.twig');
	}

	/**
	 * @return Response
	 * @Route("/tijdloos/lustrumreis")
	 * @Auth(P_LOGGED_IN)
	 */
	public function lustrumreis(): Response
	{
		return $this->render('lustrum12/lustrumreis.html.twig');
	}

	/**
	 * @return Response
	 * @Route("/tijdloos/slotactiviteit")
	 * @Auth(P_LOGGED_IN)
	 */
	public function hoogtijd(): Response
	{
		return $this->render('lustrum12/slotactiviteit.html.twig');
	}

	/**
	 * @return Response
	 * @Route("/tijdloos/gala")
	 * @Auth(P_LOGGED_IN)
	 */
	public function lustrumgala(): Response
	{
		return $this->render('lustrum12/lustrumgala.html.twig');
	}
}
