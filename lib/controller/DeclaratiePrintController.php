<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\entity\declaratie\Declaratie;
use CsrDelft\service\DeclaratiePDFGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class DeclaratiePrintController extends AbstractController
{
	/**
	 * @Route("/declaratie/print/{declaratie}", name="declaratie_print", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function print(Declaratie $declaratie, DeclaratiePDFGenerator $declaratiePDFGenerator): Response
	{
		if (!$declaratie->magBeoordelen() || !$declaratie->isGoedgekeurd()) {
			throw $this->createAccessDeniedException();
		}

		list($type, $content) = $declaratiePDFGenerator->genereerDeclaratie($declaratie);
		$response = new Response($content);

		$disposition = $response->headers->makeDisposition(
			ResponseHeaderBag::DISPOSITION_INLINE,
			"{$declaratie->getTitel()}.{$type}"
		);
		$response->headers->set('Content-Disposition', $disposition);
		$response->headers->set('Content-Type', "application/{$type}");

		return $response;
	}
}
