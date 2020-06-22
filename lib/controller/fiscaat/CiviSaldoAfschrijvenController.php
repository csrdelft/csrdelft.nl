<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\repository\fiscaat\CiviSaldoRepository;
use CsrDelft\view\renderer\TemplateView;
use ParseCsv\Csv;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
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

	private function quickMelding($melding, $code, $url = '/fiscaat/afschrijven') {
		setMelding($melding, $code);
		return $this->redirect($url);
	}

	/**
	 * @Route("/fiscaat/afschrijven/upload", methods={"POST"})
	 * @Auth(P_FISCAAT_MOD)
	 * @param Request $request
	 * @return Response
	 */
	public function upload(Request $request, Session $session) {
		// Kijk of bestand meegegeven is
		if (!$request->files->has('csv')) {
			return $this->quickMelding("Upload een CSV", 2);
		}

		// Kijk of bestand CSV is
		/** @var UploadedFile $file */
		$file = $request->files->get('csv');
		if (!in_array($file->getMimeType(), ['text/plain', 'text/csv', 'application/vnd.ms-excel'])) {
			return $this->quickMelding("Alleen een CSV is toegestaan", 2);
		}

		// Parse CSV
		$csv = new Csv();
		if ($csv->auto($file->getPathname()) === false) {
			return $this->quickMelding("Fout bij inlezen van CSV", 2);
		}
		$data = $csv->data;

		// Controleer of er regels zijn en eerste regel geldige keys heeft
		if (empty($data) === 0) {
			return $this->quickMelding("Geen regels gevonden", 2);
		}
		if (array_keys($data[0]) !== ['uid', 'productID', 'aantal', 'beschrijving']) {
			return $this->quickMelding("Ongeldige kolommen in de CSV", 2);
		}

		// Sla data op in sessie
		$key = uniqid_safe();
		$session->set("afschrijven-{$key}", $data);

		// Redirect naar check pagina
		return $this->redirect('/fiscaat/afschrijven/controle/' . $key);
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
