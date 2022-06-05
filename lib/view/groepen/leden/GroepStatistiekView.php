<?php

namespace CsrDelft\view\groepen\leden;

use CsrDelft\common\CsrException;
use CsrDelft\entity\Geslacht;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\GroepStatistiekDTO;
use DateTime;
use function array_key_first;
use function array_key_last;

class GroepStatistiekView extends GroepTabView
{
	/**
	 * @var GroepStatistiekDTO
	 */
	private $statistiek;

	public function __construct(Groep $groep, GroepStatistiekDTO $statistiek)
	{
		parent::__construct($groep);
		$this->statistiek = $statistiek;
	}

	private function verticale($data)
	{
		$verticalen = [];
		$deelnemers = [];
		foreach ($data as $row) {
			$verticalen[] = $row['naam'];
			$deelnemers[] = $row['aantal'];
		}

		return htmlentities(
			json_encode([
				'labels' => $verticalen,
				'datasets' => [
					[
						'label' => '# van verticale',
						'data' => $deelnemers,
					],
				],
			])
		);
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
		return htmlentities(
			json_encode([
				'labels' => ['Mannen', 'Vrouwen'],
				'datasets' => [
					[
						'label' => '# mannen en vrouwen',
						'data' => [$mannen, $vrouwen],
						'backgroundColor' => ['#AFD8F8', '#FFCBDB'],
					],
				],
			])
		);
	}

	private function lichting($data)
	{
		$aantal = [];
		$lichting = [];
		foreach ($data as $row) {
			$aantal[] = (int) $row['aantal'];
			$lichting[] = (int) $row['lidjaar'];
		}

		return htmlentities(
			json_encode([
				'labels' => $lichting,
				'datasets' => [
					[
						'label' => 'Aantal',
						'data' => $aantal,
					],
				],
			])
		);
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

		return htmlentities(
			json_encode([
				'labels' => [$begin, $eind],
				'datasets' => [
					[
						'label' => 'Aantal over tijd',
						'fill' => false,
						'data' => $series,
					],
				],
			])
		);
	}

	/**
	 * @return string
	 * @throws CsrException
	 */
	public function getTabContent()
	{
		$verticale = $this->verticale($this->statistiek->verticale);
		$geslacht = $this->geslacht($this->statistiek->geslacht);
		$lichting = $this->lichting($this->statistiek->lichting);
		$tijd = $this->tijd($this->statistiek->tijd);
		$totaal = $this->statistiek->totaal;

		return <<<HTML
<h4>Verticale</h4>
<div class="ctx-graph-pie" data-data="{$verticale}"></div>
<h4>Geslacht</h4>
<div class="ctx-graph-pie" data-data="{$geslacht}"></div>
<h4>Lichting</h4>
<div class="ctx-graph-bar" data-data="{$lichting}"></div>
<h4>Tijd</h4>
<div class="ctx-graph-line" data-data="{$tijd}"></div>
<h4>Totaal</h4>
{$totaal}

HTML;
	}
}
