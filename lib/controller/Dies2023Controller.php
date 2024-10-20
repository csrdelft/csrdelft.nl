<?php

namespace CsrDelft\controller;

use Symfony\Component\Routing\Attribute\Route;
use CsrDelft\common\Annotation\Auth;
use Symfony\Component\HttpFoundation\Response;

class Dies2023Controller extends AbstractController
{
	/**
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/magnumopus')]
	public function lustrum()
	{
		return $this->render('dies2023/dies2023.html.twig');
	}
}
