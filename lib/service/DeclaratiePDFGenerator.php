<?php

namespace CsrDelft\service;

use CsrDelft\entity\declaratie\Declaratie;
use CsrDelft\entity\declaratie\DeclaratieBon;
use Com\Tecnick\Pdf\Tcpdf;
use Twig\Environment;

class DeclaratiePDFGenerator
{
	public function __construct(
		private readonly Environment $twig
	) {
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
		// PDF metadata
		$pdf = new Tcpdf();
		$pdf->SetCreator('csrdelft.nl');
		$pdf->SetAuthor('C.S.R. Delft');
		$pdf->SetTitle($declaratie->getTitel());

		// Declaratie informatie
		$declaratieInhoud = $this->twig->render('declaratie/print.html.twig', [
			'declaratie' => $declaratie,
		]);

		$pagina = $pdf->AddPage();

		$marge = 15;
		$pdf->addHTMLCell(
			html: $declaratieInhoud,
			posx: $marge,
			posy: 20,
			width: $pagina['width'] - (2 * $marge),
		);

		try {
			return $pdf->getOutPDFString();
		} catch (\Throwable $e) {
			return "Error bij het genereren van declaratie-info: " . $e->getMessage();
		}
	}

	public function genereerBon(DeclaratieBon $bon): string
	{
		$filename = DECLARATIE_PATH . $bon->getBestand();
		if ($bon->isPDF()) {
			return file_get_contents($filename);
		}

		$declaratie = $bon->getDeclaratie();
		$pdf = new Tcpdf();

		// PDF metadata
		$pdf->SetCreator('csrdelft.nl');
		$pdf->SetAuthor('C.S.R. Delft');
		$pdf->SetTitle($declaratie->getTitel());

		// Bon informatie
		$this->correctImageOrientation($filename);

		[$width, $height] = getimagesize($filename);
		$aspectImage = $width / $height;

		$pagina = $pdf->AddPage();
		$aspectPage = $pagina['width'] / $pagina['height'];

		if ($aspectImage > $aspectPage) {
			// Breder dan pagina, gebruik breedte
			$imgWidth = $pagina['width'];
			$imgHeight = $imgWidth / $aspectImage;
		} else {
			// Smaller dan pagina, gebruik hoogte
			$imgHeight = $pagina['height'];
			$imgWidth = $imgHeight * $aspectImage;
		}

		try {
			$plaatjeId = $pdf->image->add($filename);
			$plaatjeContent = $pdf->image->getSetImage(
				$plaatjeId,
				xpos: 0,
				ypos: 0,
				width: $imgWidth,
				height: $imgHeight,
				pageheight: $pagina['height'],
			);

			$pdf->page->addContent($plaatjeContent);

			return $pdf->getOutPDFString();
		} catch (\Throwable $e) {
			return "Error bij het genereren van declaratie-bon: " . $e->getMessage();
		}
	}

	/**
	 * Exporteert een pdf van de gegeven declaratie.
	 *
	 * Declaraties-exports hebben een eerste pagina met gegevens (gegenereerd
	 * door `genereerDeclaratieInfo()`). Vervolgens zijn alle bonnen
	 * (afbeeldingen of pdf's) elk op een eigen pagina toegevoegd.
	 *
	 * @param Declaratie $declaratie
	 * @return array|string[]
	 */
	public function genereerDeclaratie(Declaratie $declaratie): array
	{
		try {
			$pdf = new Tcpdf();
			$pdf->setCreator('csrdelft.nl');
			$pdf->setAuthor('C.S.R. Delft');
			$pdf->setTitle($declaratie->getTitel());

			// Voeg info-pagina toe
			$infoSourceId = $pdf->setImportSourceData($this->genereerDeclaratieInfo($declaratie));
			$pdf->appendDocument($infoSourceId);

			// Voeg alle pagina's met bonnetjes toe
			foreach ($declaratie->getBonnen() as $declaratieBon) {
				$bonSourceId = $pdf->setImportSourceData($this->genereerBon($declaratieBon));
				$pdf->appendDocument($bonSourceId);
			}

			$merged = $pdf->getOutPDFString();

			return ['pdf', $merged];
		} catch (\Throwable $e) {
			return [
				'txt',
				'Er ging iets fout bij het genereren van de PDF: ' . $e->getMessage(),
			];
		}
	}
}
