<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArroController extends AbstractController
{
	/**
	 * @return Response
	 * @Route("/arro")
	 * @Auth(P_LOGGED_IN)
	 */
	public function arro()
	{
		$now = new DateTimeImmutable();
		$date = new DateTimeImmutable('2022-11-10T21:00:00Z');
		if ($now < $date) {
			return $this->redirect('/documenten/categorie/5');
		}
		return $this->render('arro/index.html.twig');
	}
}
