<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\view\renderer\TemplateView;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DeclaratieController extends AbstractController {
	/**
	 * @return TemplateView
	 * @Route("/declaratie/nieuw", name="declaratie_nieuw", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function nieuw() {
		return view('declaratie.nieuw');
	}
}
