<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Dies2026Controller extends AbstractController
{
	/**
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/flamenco')]
	public function lustrum()
	{
		return $this->render('dies2026/dies2026.html.twig');
	}
}
