<?php

namespace CsrDelft\controller;

use CsrDelft\controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use CsrDelft\common\Annotation\Auth;

class CiviMelderController extends AbstractController
{
	public function __construct(){
	}
	/**
	 * @return Response
	 * @Route("/civimelder", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function mijnActiviteiten()
	{
//		return new Response("Test 123 ");
		return $this->render('civimelder/mijn_activiteiten.html.twig', []);
	}
}
