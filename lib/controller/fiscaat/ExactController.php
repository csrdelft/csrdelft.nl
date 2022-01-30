<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\service\exact\Exact;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/fiscaat/exact");
 */
class ExactController extends AbstractController
{
	/**
	 * @Route("", methods={"GET"})
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function overzicht(Exact $exact): Response
	{
		$connection = $exact->loadConnection();

		return $this->render('fiscaat/exact/overzicht.html.twig', [
			'verbonden' => $connection !== null,
			'connection' => $connection->get('current/Me'),
		]);
	}

	/**
	 * @Route("/verbind", methods={"GET"}, name="exact_login")
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function verbind(Exact $exact): Response
	{
		return $this->redirect($exact->setupConnection()->getAuthUrl());
	}

	/**
	 * @Route("/callback", methods={"GET"})
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function callback(Exact $exact, Request $request): Response
	{
		$code = $request->query->get('code');
		$connection = $exact->createConnection($code);

		return $this->json([
			'division' => $connection->getDivision()
		]);
	}
}
