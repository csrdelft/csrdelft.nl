<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\entity\declaratie\Declaratie;
use CsrDelft\service\DeclaratiePDFGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Transliterator;

class DeclaratiePrintController extends AbstractController
{
	/**
	 * @Auth(P_LOGGED_IN)
	 */
	#[
		Route(
			path: '/declaratie/print/{declaratie}',
			name: 'declaratie_print',
			methods: ['GET']
		)
	]
	public function print(
		Declaratie $declaratie,
		DeclaratiePDFGenerator $declaratiePDFGenerator
	): Response {
		if (!$declaratie->magBeoordelen() || !$declaratie->isGoedgekeurd()) {
			throw $this->createAccessDeniedException();
		}

		[$type, $content] = $declaratiePDFGenerator->genereerDeclaratie(
			$declaratie
		);
		$response = new Response($content);

		$transliterator = Transliterator::createFromRules(
			':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: Lower(); :: NFC;',
			Transliterator::FORWARD
		);
		$safeName = $transliterator->transliterate($declaratie->getTitel());

		$disposition = $response->headers->makeDisposition(
			ResponseHeaderBag::DISPOSITION_ATTACHMENT,
			"{$declaratie->getTitel()}.{$type}",
			"{$safeName}.{$type}"
		);
		$response->headers->set('Content-Disposition', $disposition);
		$response->headers->set('Content-Type', "application/{$type}");

		return $response;
	}
}
