<?php

namespace CsrDelft\view\groepen\leden;

use CsrDelft\common\CsrException;

class GroepStatistiekView extends GroepTabView {

	private function verticale($data) {
		$verticalen = [];
		$deelnemers = [];
		foreach ($data as $row) {
			$verticalen[] = $row[0];
			$deelnemers[] = $row[1];
		}

		return htmlentities(json_encode([
			'labels' => $verticalen,
			'datasets' => [
				[
					'label' => '# van verticale',
					'data' => $deelnemers,
				]
			]
		]));
	}

	private function geslacht($data) {
		$mannen = 0;
		$vrouwen = 0;
		foreach ($data as $row) {
			switch ($row[0]) {
				case 'm':
					$mannen = $row[1];
					break;
				case 'v':
					$vrouwen = $row[1];
					break;
			}
		}
		return htmlentities(json_encode([
			'labels' => ['Mannen', 'Vrouwen'],
			'datasets' => [
				[
					'label' => '# mannen en vrouwen',
					'data' => [$mannen, $vrouwen],
					'backgroundColor' => ['#AFD8F8', '#FFCBDB'],
				]
			]
		]));
	}

	private function lichting($data) {
		$aantal = [];
		$lichting = [];
		foreach ($data as $row) {
			$aantal[] = (int)$row[1];
			$lichting[] = (int)$row[0];
		}

		return htmlentities(json_encode([
			'labels'=> $lichting,
			'datasets' => [
				[
					'label' => 'Aantal',
					'data' => $aantal,
				]
			]
		]));
	}

	private function tijd($data) {
		$totaal = 0;
		$series = [];
		foreach ($data as $tijd => $aantal) {
			$totaal += $aantal;
			$series[] = ["t" => date("c", $tijd), "y" => $totaal];
		}

		return htmlentities(json_encode([
			'labels' => ['Aantal'],
			'datasets' => [
				[
					'label' => 'Aantal over tijd',
					'data' => $series,
				]
			]
		]));
	}

	/**
	 * @return string
	 * @throws CsrException
	 */
	public function getTabContent() {
		$statistieken = $this->groep->getStatistieken();

		$verticale = $this->verticale($statistieken['verticale']);
		$geslacht = $this->geslacht($statistieken['geslacht']);
		$lichting = $this->lichting($statistieken['lichting']);
		$tijd = $this->tijd($statistieken['tijd']);
		$totaal = $statistieken['totaal'];

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
