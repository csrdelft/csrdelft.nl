<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AftelController extends AbstractController
{
	/**
	 * @return Response
	 * @Route("/aftel", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function doorverwijs()
	{
		if (isset($_ENV['AFTEL_EIND']) && time() >= $_ENV['AFTEL_EIND'] - 3) {
			return $this->redirect($_ENV['AFTEL_URL']);
		} else {
			return $this->redirect('/');
		}
	}
}
