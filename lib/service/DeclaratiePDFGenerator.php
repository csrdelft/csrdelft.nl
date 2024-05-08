<?php

namespace CsrDelft\service;

use Exception;
use Clegginabox\PDFMerger\PDFMerger;
use CsrDelft\entity\declaratie\Declaratie;
use CsrDelft\entity\declaratie\DeclaratieBon;
use Symfony\Component\Filesystem\Filesystem;
use TCPDF;
use Twig\Environment;
//use ZipArchive;

class DeclaratiePDFGenerator
{
	/**
	 * @var Environment
	 */
	private $twig;
	/**
	 * @var Filesystem
	 */
	private $filesystem;

	public function __construct(Environment $twig, Filesystem $filesystem)
	{
		$this->twig = $twig;
		$this->filesystem = $filesystem;
	}

	private function correctImageOrientation($filename)
	{
		if (function_exists('exif_read_data')) {
			$exif = exif_read_data($filename);
			if ($exif && isset($exif['Orientation'])) {
				$orientation = $exif['Orientation'];
				if ($orientation != 1) {
					$img = imagecreatefromjpeg($filename);
					$deg = 0;
					switch ($orientation) {
						case 3:
							$deg = 180;
							break;
						case 6:
							$deg = 270;
							break;
						case 8:
							$deg = 90;
							break;
					}
					if ($deg) {
						$img = imagerotate($img, $deg, 0);
					}
					// then rewrite the rotated image back to the disk as $filename
					imagejpeg($img, $filename, 95);
				}
			}
		}
	}

	public function genereerDeclaratieInfo(Declaratie $declaratie): string
	{
		// PDF informatie
		$pdf = new TCPDF();
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('C.S.R. Delft');
		$pdf->SetTitle($declaratie->getTitel());

		// PDF styling
		$pdf->setHeaderData(
			null,
			null,
			'Declaratie C.S.R. Delft (#' . $declaratie->getId() . ')',
			$declaratie->getTitel(),
			[17, 39, 58]
		);
		$pdf->SetMargins(PDF_MARGIN_LEFT, 20, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->setPrintFooter(false);
		$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
		$pdf->SetFontSize(9);

		// Declaratie informatie
		$pdf->AddPage();
		$declaratieInhoud = $this->twig->render('declaratie/print.html.twig', [
			'declaratie' => $declaratie,
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
		$this->correctImageOrientation($filename);

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

	public function genereerDeclaratie(Declaratie $declaratie): array
	{
		$location = $this->filesystem->tempnam(TMP_PATH, 'decla_');
		$declaInfo = $this->genereerDeclaratieInfo($declaratie);
		$this->filesystem->dumpFile($location, $declaInfo);
		$pdfs = [$location];

		foreach ($declaratie->getBonnen() as $i => $declaratieBon) {
			$location = $this->filesystem->tempnam(TMP_PATH, 'decla_');
			$this->filesystem->dumpFile(
				$location,
				$this->genereerBon($declaratieBon)
			);
			$pdfs[] = $location;
		}

		try {
			$pdf = new PDFMerger();
			foreach ($pdfs as $location) {
				$pdf->addPDF($location, 'all');
			}

			$merged = $pdf->merge('string');

			foreach ($pdfs as $location) {
				$this->filesystem->remove($location);
			}

			return ['pdf', $merged];
		} catch (Exception $e) {
			//			$zipTmp = tempnam(sys_get_temp_dir(), "zip");
			//			$zip = new ZipArchive();
			//			$zip->open($zipTmp, ZipArchive::OVERWRITE);
			//			foreach ($pdfs as $index => $location) {
			//				$nummer = $index + 1;
			//				$zip->addFromString("{$declaratie->getTitel()} - {$nummer}.pdf", file_get_contents($location));
			//			}
			//			$filename = $zip->filename;
			//			$zip->close();
			//
			//			$data = ['zip', file_get_contents($filename)];
			//			unlink($zipTmp);
			$data = [
				'txt',
				'Er ging iets fout bij het genereren van de PDF: ' . $e->getMessage(),
			];
			return $data;
		}
	}
}
