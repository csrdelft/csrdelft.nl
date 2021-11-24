<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\entity\declaratie\Declaratie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use TCPDF;
use Twig\Environment;

class DeclaratiePrintController extends AbstractController
{
	/**
	 * @param Declaratie $declaratie
	 * @return Response
	 * @Route("/declaratie/print/{declaratie}", name="declaratie_print", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function print(Declaratie $declaratie, Environment $twig): Response
	{
		if (!$declaratie->magBeoordelen() || !$declaratie->isGoedgekeurd()) {
			throw $this->createAccessDeniedException();
		}

		$pdf = new TCPDF();

		// PDF informatie
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('C.S.R. Delft');
		$pdf->SetTitle($declaratie->getTitel());

		// PDF styling
		$pdf->setHeaderData( null, null, 'Declaratie C.S.R. Delft', $declaratie->getTitel(), [17, 39, 58]);
		$pdf->SetMargins(PDF_MARGIN_LEFT, 20, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);

		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->SetFontSize(9);

		// Declaratie informatie
		$pdf->AddPage();
		$declaratieInhoud = $twig->render('declaratie/print.html.twig', [
			'declaratie' => $declaratie
		]);
		$pdf->writeHTML($declaratieInhoud);

		// Output
		$content = $pdf->Output('declaratie.pdf', 'S');
		$response = new Response($content);

		$disposition = $response->headers->makeDisposition(
			ResponseHeaderBag::DISPOSITION_INLINE,
			"{$declaratie->getTitel()}.pdf"
		);
		$response->headers->set('Content-Disposition', $disposition);
		$response->headers->set('Content-Type', 'application/pdf');

		return $response;
	}
}
