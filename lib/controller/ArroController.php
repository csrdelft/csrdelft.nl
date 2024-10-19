<?php

namespace CsrDelft\controller;

use Symfony\Component\Routing\Attribute\Route;
use CsrDelft\common\Annotation\Auth;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

class ArroController extends AbstractController
{
	/**
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/arro')]
	public function arro()
	{
		$now = new DateTimeImmutable();
		$date = new DateTimeImmutable('2023-02-03T21:00:00Z');
		if ($now < $date) {
			return $this->redirect('/documenten/categorie/5');
		}
		return $this->render('arro/index.html.twig');
	}
}
