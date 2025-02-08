<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AftelController extends AbstractController
{
	/**
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/aftel', methods: ['GET'])]
	public function doorverwijs()
	{
		if (isset($_ENV['AFTEL_EIND']) && time() >= $_ENV['AFTEL_EIND'] - 3) {
			return $this->redirect($_ENV['AFTEL_URL']);
		} else {
			return $this->redirect('/');
		}
	}
}
