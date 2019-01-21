<?php

namespace CsrDelft\view\groepen\leden;

use CsrDelft\common\CsrException;

class GroepStatistiekView extends GroepTabView {

	private function verticale($data) {
		$series = array();
		foreach ($data as $row) {
			$series[] = [$row[0], $row[1]];
		}

		return htmlentities(json_encode($series));
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
		return htmlentities(json_encode([['Mannen', $mannen], ['Vrouwen', $vrouwen]]));
	}

	private function lichting($data) {
		$series = [];
		$moment = [];
		foreach ($data as $row) {
			$series[] = (int)$row[1];
			$moment[] = (int)$row[0];
		}

		return htmlentities(json_encode([$moment, $series]));
	}

	private function tijd($data) {
		$series = ['Aantal'];
		$moment = ['x'];
		$totaal = 0;
		foreach ($data as $tijd => $aantal) {
			$totaal += $aantal;
			$moment[] = $tijd;
			$series[] = $totaal;
		}

		return htmlentities(json_encode([$moment, $series]));
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
