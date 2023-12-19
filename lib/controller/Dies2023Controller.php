<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Dies2023Controller extends AbstractController
{
	/**
	 * @return Response
	 * @Route("/magnumopus")
	 * @Auth(P_LOGGED_IN)
	 */
	public function lustrum()
	{
		return $this->render('dies2023/dies2023.html.twig');
	}
}
