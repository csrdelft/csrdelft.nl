<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class DeclaratieController extends AbstractController {
	/**
	 * @return Response
	 * @Route("/declaratie/nieuw", name="declaratie_nieuw", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function nieuw() {
		return $this->render('declaratie/nieuw.html.twig');
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @Route("/declaratie/upload", name="declaratie_upload", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function upload(Request $request) {
		$key = uniqid();

		$file = $request->files->get('bon');
		if (!$file) {
			throw new BadRequestHttpException('Geen bestand geselecteerd');
		}

		return $this->json([
			'key' => $key
		]);
	}
}
