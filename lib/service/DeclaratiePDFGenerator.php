<?php

namespace CsrDelft\service;

use Clegginabox\PDFMerger\PDFMerger;
use CsrDelft\entity\declaratie\Declaratie;
use CsrDelft\entity\declaratie\DeclaratieBon;
use TCPDF;
use Twig\Environment;
use ZipArchive;

class DeclaratiePDFGenerator
{
	/**
	 * @var Environment
	 */
	private $twig;

	public function __construct(Environment $twig) {
		$this->twig = $twig;
	}

	public function genereerDeclaratieInfo(Declaratie $declaratie): string
	{
		// PDF informatie
		$pdf = new TCPDF();
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('C.S.R. Delft');
		$pdf->SetTitle($declaratie->getTitel());

		// PDF styling
		$pdf->setHeaderData( null, null, 'Declaratie C.S.R. Delft', $declaratie->getTitel(), [17, 39, 58]);
		$pdf->SetMargins(PDF_MARGIN_LEFT, 20, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->setPrintFooter(false);
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->SetFontSize(9);

		// Declaratie informatie
		$pdf->AddPage();
		$declaratieInhoud = $this->twig->render('declaratie/print.html.twig', [
			'declaratie' => $declaratie
		]);
		$pdf->writeHTML($declaratieInhoud);

		// Output
		return $pdf->Output('declaratie.pdf', 'S');
	}

	public function genereerBon(DeclaratieBon $bon): string
	{
		$filename = DECLARATIE_PATH . $bon->getBestand();
		if ($bon->isPDF()) {
			return file_get_contents($filename);
		}

		$declaratie = $bon->getDeclaratie();
		$pdf = new TCPDF();

		// PDF informatie
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('C.S.R. Delft');
		$pdf->SetTitle($declaratie->getTitel());

		// PDF styling
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetMargins(0, 0, 0);

		// Bon informatie
		$pdf->AddPage();


		list($width, $height) = getimagesize($filename);
		$aspectImage = $width / $height;
		$aspectPage = $pdf->getPageWidth() / $pdf->getPageHeight();
		if ($aspectImage > $aspectPage) {
			// Breder dan pagina, gebruik breedte
			$pdf->Image($filename, 0, 0, $pdf->getPageWidth());
		} else {
			// Smaller dan pagina, gebruik hoogte
			$pdf->Image($filename, 0, 0, 0, $pdf->getPageHeight());
		}

		// Output
		return $pdf->Output('declaratie.pdf', 'S');
	}

	public function genereerDeclaratie(Declaratie $declaratie) {
		$info = tmpfile();
		$declaInfo = $this->genereerDeclaratieInfo($declaratie);
		fwrite($info, $declaInfo);
		$location = stream_get_meta_data($info)['uri'];

		$bonnen = [];
		$pdfs = [$location];

		foreach ($declaratie->getBonnen() as $i => $declaratieBon) {
			$bonnen[$i] = tmpfile();
			fwrite($bonnen[$i], $this->genereerBon($declaratieBon));
			$location = stream_get_meta_data($bonnen[$i])['uri'];
			$pdfs[] = $location;
		}

		try {
			$pdf = new PDFMerger();
			foreach ($pdfs as $location) {
				$pdf->addPDF($location, 'all');
			}

			return ['pdf', $pdf->merge('string')];
		} catch (\Exception $e) {
			$zipTmp = tempnam(sys_get_temp_dir(), "zip");
			$zip = new ZipArchive();
			$zip->open($zipTmp, ZipArchive::OVERWRITE);
			foreach ($pdfs as $index => $location) {
				$nummer = $index + 1;
				$zip->addFromString("{$declaratie->getTitel()} - {$nummer}.pdf", file_get_contents($location));
			}
			$filename = $zip->filename;
			$zip->close();

			$data = ['zip', file_get_contents($filename)];
			unlink($zipTmp);
			return $data;
		}
	}
}
