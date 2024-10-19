<?php

namespace CsrDelft\controller;

use Symfony\Component\Routing\Attribute\Route;
use CsrDelft\common\Annotation\Auth;
use Symfony\Component\HttpFoundation\Response;

class Dies2024Controller extends AbstractController
{
	/**
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/lumiere')]
	public function lustrum()
	{
		return $this->render('dies2024/dies2024.html.twig');
	}
}
