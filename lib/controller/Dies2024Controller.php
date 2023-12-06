<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Dies2024Controller extends AbstractController
{
	/**
	 * @return Response
	 * @Route("/lumiere")
	 * @Auth(P_LOGGED_IN)
	 */
	public function lustrum()
	{
		return $this->render('dies2024/dies2024.html.twig');
	}
}
