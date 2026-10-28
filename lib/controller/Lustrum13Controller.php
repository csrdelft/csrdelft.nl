<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Lustrum13Controller extends AbstractController
{
	/**
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/panorama', name: 'lustrum13')]
	public function lustrum(): Response
	{
		$response = $this->render('lustrum13/index.html.twig');
		$response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
		$response->headers->set('Cache-Control', 'private, no-store');
		return $response;
	}
}
