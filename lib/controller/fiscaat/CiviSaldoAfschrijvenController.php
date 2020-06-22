<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\repository\fiscaat\CiviSaldoRepository;
use CsrDelft\view\renderer\TemplateView;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CiviSaldoAfschrijvenController extends AbstractController {
	/** @var CiviSaldoRepository */
	private $civiSaldoRepository;

	public function __construct(CiviSaldoRepository $civiSaldoRepository) {
		$this->civiSaldoRepository = $civiSaldoRepository;
	}

	/**
	 * @return TemplateView
	 * @Route("/fiscaat/afschrijven")
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function afschrijven() {
		return view('fiscaat.afschrijven', []);
	}

	/**
	 * @return Response
	 * @Route("/fiscaat/afschrijven/template")
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function downloadTemplate() {
		$template = "uid;productID;aantal;beschrijving\r\nx101;32;100;Lunch";
		$response = new Response($template);
		$disposition = HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, 'afschrijven.csv');
		$response->headers->set('Content-Type', 'text/csv');
		$response->headers->set('Content-Disposition', $disposition);
		return $response;
	}
}
