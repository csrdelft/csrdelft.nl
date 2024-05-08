<?php

namespace CsrDelft\view\groepen\leden;

use CsrDelft\entity\Geslacht;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\GroepStatistiekDTO;
use CsrDelft\view\ToHtmlResponse;
use CsrDelft\view\ToResponse;
use DateTime;
use Twig\Environment;

class GroepStatistiekView implements ToResponse
{
	use ToHtmlResponse;

	/**
	 * @var GroepStatistiekDTO
	 */
	private $statistiek;
	/**
	 * @var Environment
	 */
	private $twig;
	/**
	 * @var Groep
	 */
	private $groep;

	public function __construct(
		Environment $twig,
		Groep $groep,
		GroepStatistiekDTO $statistiek
	) {
		$this->statistiek = $statistiek;
		$this->twig = $twig;
		$this->groep = $groep;
	}

	public function __toString(): string
	{
		return $this->twig->render('groep/statistiek.html.twig', [
			'groep' => $this->groep,
			'verticale' => $this->verticale($this->statistiek->verticale),
			'geslacht' => $this->geslacht($this->statistiek->geslacht),
			'lichting' => $this->lichting($this->statistiek->lichting),
			'tijd' => $this->tijd($this->statistiek->tijd),
			'totaal' => $this->statistiek->totaal,
		]);
	}

	private function verticale($data)
	{
		$verticalen = [];
		$deelnemers = [];
		foreach ($data as $row) {
			$verticalen[] = $row['naam'];
			$deelnemers[] = $row['aantal'];
		}

		return json_encode([
			'labels' => $verticalen,
			'datasets' => [
				[
					'label' => '# van verticale',
					'data' => $deelnemers,
				],
			],
		]);
	}

	private function geslacht($data)
	{
		$mannen = 0;
		$vrouwen = 0;
		foreach ($data as $row) {
			switch ($row['geslacht']->getValue()) {
				case Geslacht::Man:
					$mannen = $row['aantal'];
					break;
				case Geslacht::Vrouw:
					$vrouwen = $row['aantal'];
					break;
			}
		}
		return json_encode([
			'labels' => ['Mannen', 'Vrouwen'],
			'datasets' => [
				[
					'label' => '# mannen en vrouwen',
					'data' => [$mannen, $vrouwen],
					'backgroundColor' => ['#AFD8F8', '#FFCBDB'],
				],
			],
		]);
	}

	private function lichting($data)
	{
		$aantal = [];
		$lichting = [];
		foreach ($data as $row) {
			$aantal[] = (int) $row['aantal'];
			$lichting[] = (int) $row['lidjaar'];
		}

		return json_encode([
			'labels' => $lichting,
			'datasets' => [
				[
					'label' => 'Aantal',
					'data' => $aantal,
				],
			],
		]);
	}

	private function tijd($data)
	{
		$totaal = 0;
		$series = [];
		foreach ($data as $tijd => $aantal) {
			$totaal += $aantal;
			$series[] = ['t' => date(DateTime::RFC2822, $tijd), 'y' => $totaal];
		}

		$begin = date(DateTime::RFC2822, array_key_first($data));
		$eind = date(DateTime::RFC2822, array_key_last($data));

		return json_encode([
			'labels' => [$begin, $eind],
			'datasets' => [
				[
					'label' => 'Aantal over tijd',
					'fill' => false,
					'data' => $series,
				],
			],
		]);
	}
}
