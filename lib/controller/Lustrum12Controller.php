<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Lustrum12Controller extends AbstractController
{
	/**
  * @return Response
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/tijdloos')]
 public function lustrum(): Response
	{
		return $this->render('lustrum12/index.html.twig');
	}

	/**
  * @return Response
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/tijdloos/thema')]
 public function LustrumThema(): Response
	{
		return $this->render('lustrum12/thema.html.twig');
	}

	/**
  * @return Response
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/tijdloos/opening')]
 public function lustrumOpening(): Response
	{
		return $this->render('lustrum12/opening.html.twig');
	}

	/**
  * @return Response
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/tijdloos/lustrumweek')]
 public function lustrumWeek(): Response
	{
		return $this->render('lustrum12/lustrumweek.html.twig');
	}

	/**
  * @return Response
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/tijdloos/terugnaardetoekomst')]
 public function lustrumWeek2(): Response
	{
		return $this->render('lustrum12/lustrumweek2.html.twig');
	}

	/**
  * @return Response
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/tijdloos/zingmee')]
 public function lustrumActiviteitCantus(): Response
	{
		return $this->render('lustrum12/zingmee.html.twig');
	}

	/**
  * @return Response
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/tijdloos/dikkemerch')]
 public function lustrumMerch(): Response
	{
		return $this->render('lustrum12/dikkemerch.html.twig');
	}

	/**
  * @return Response
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/tijdloos/diesthema')]
 public function diesThema(): Response
	{
		return $this->render('lustrum12/diesthema.html.twig');
	}

	/**
  * @return Response
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/tijdloos/dies')]
 public function dies(): Response
	{
		return $this->render('lustrum12/dies.html.twig');
	}

	/**
  * @return Response
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/tijdloos/dies/gala')]
 public function diesgala(): Response
	{
		return $this->render('lustrum12/diesgala.html.twig');
	}

	/**
  * @return Response
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/tijdloos/dies/etiquette')]
 public function etiquette(): Response
	{
		return $this->render('lustrum12/etiquette.html.twig');
	}

	/**
  * @return Response
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/tijdloos/lustrumreis/inschrijven')]
 public function lustrumreisinschrijven(): Response
	{
		return $this->render('lustrum12/lustrumreisinschrijven.html.twig');
	}

	/**
  * @return Response
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/tijdloos/lustrumreis')]
 public function lustrumreis(): Response
	{
		return $this->render('lustrum12/lustrumreis.html.twig');
	}

	/**
  * @return Response
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/tijdloos/slotactiviteit')]
 public function hoogtijd(): Response
	{
		return $this->render('lustrum12/slotactiviteit.html.twig');
	}

	/**
  * @return Response
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/tijdloos/gala')]
 public function lustrumgala(): Response
	{
		return $this->render('lustrum12/lustrumgala.html.twig');
	}
}
