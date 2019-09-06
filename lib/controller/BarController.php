<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\service\BarService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BarController extends AbstractController {
	protected $barService;

	public function __construct(BarService $barService) {
		$this->barService = $barService;
	}

	/**
	 * Barsysteem main request.
	 * @Route("/bar", methods={"GET"})
	 * @Auth(P_PUBLIC)
	 */
	public function main() {
		// Laad Vue app.
		return view('bar', [
			"CsrfToken" => $this->barService->getCsrfToken()
		]);
	}
}
