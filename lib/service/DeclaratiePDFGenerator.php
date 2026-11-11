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

	/**
	 * Helperfunctie om een nieuw tc-lib-pdf instance te krijgen met juiste instellingen
	 * @throws \Exception Als de configuratie incorrect is of de helvetica-font niet geïmporteerd kan worden
	 */
	private function nieuwPdf(string $titel): Tcpdf {
		$pdf = new Tcpdf(
			fileOptions: [
				// geef tcpdf toegang tot bonnetjes van decla's en de (in CI) gecompilede fonts.
				'allowedPaths' => [
					realpath(dirname(__DIR__, 2) . '/data/declaraties'),
					realpath(dirname(__DIR__, 2) . '/vendor/tecnickcom/tc-lib-pdf-font/target/fonts'),
				],
			]
		);

		$pdf->SetCreator('csrdelft.nl');
		$pdf->SetAuthor('C.S.R. Delft');
		$pdf->SetTitle($titel);
		$pdf->font->insert($pdf->pon, 'helvetica', '', 9);

		return $pdf;
	}

	/**
	 * Draait de image zoals aangegeven in de exif-metadata en vervangt het originele bestand,
	 * zodat tc-lib-pdf het rechtstreeks kan importeren.
	 * @param $filename
	 * @return void
	 */
	private function correctImageOrientation($filename): void
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

	/**
	 * Genereert een info-pagina voor de declaratie (de eerste pagina)
	 *
	 * @param Declaratie $declaratie
	 * @return string PDF-bestand als string
	 * @throws \Throwable
	 */
	public function genereerDeclaratieInfo(Declaratie $declaratie): string
	{
		$pdf = $this->nieuwPdf($declaratie->getTitel());

		// Render declaratie-informatie
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

		return $pdf->getOutPDFString();
	}

	/**
	 * Genereert een PDF-string voor een bon in de declaratie (afbeelding of pdf)
	 *
	 * @throws \Throwable Allerlei tc-lib-pdf errors, in principe alleen als het bestand van de bon niet kan worden
	 * 										geopend of er een breaking change in de pdf is geweest.
	 */
	public function genereerBon(DeclaratieBon $bon): string
	{
		$filename = DECLARATIE_PATH . $bon->getBestand();
		if ($bon->isPDF()) {
			$pdf_file = file_get_contents($filename);
			if (!$pdf_file) {
				throw new \Exception("Gelinkte PDF-bestand $filename van declaratiebon {$bon->getId()} kan niet worden geopend.");
			}
			return $pdf_file;
		}

		$declaratie = $bon->getDeclaratie();
		$pdf = $this->nieuwPdf($declaratie->getTitel());

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
	}

	/**
	 * Exporteert een pdf van de gegeven declaratie.
	 *
	 * Declaraties-exports hebben een eerste pagina met gegevens (gegenereerd
	 * door `genereerDeclaratieInfo()`). Vervolgens zijn alle bonnen
	 * (afbeeldingen of pdf's) elk op een eigen pagina toegevoegd.
	 *
	 * @param Declaratie $declaratie
	 * @return string[] Een array met 2 strings: bestandstype en het bestand als string
	 * @throws \Throwable
	 */
	public function genereerDeclaratie(Declaratie $declaratie): array
	{
		$pdf = $this->nieuwPdf($declaratie->getTitel());

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
	}
}
