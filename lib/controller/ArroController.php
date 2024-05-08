<?php

namespace CsrDelft\controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
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
	public function arro(): RedirectResponse|Response
	{
		$now = new DateTimeImmutable();
		$date = new DateTimeImmutable('2023-02-03T21:00:00Z');
		if ($now < $date) {
			return $this->redirect('/documenten/categorie/5');
		}
		return $this->render('arro/index.html.twig');
	}
}
