<?php

namespace CsrDelft\view\groepen\leden;

use CsrDelft\common\CsrException;
use CsrDelft\view\groepen;

class GroepStatistiekView extends groepen\leden\GroepTabView {

	private function verticale($data) {
		$series = array();
		foreach ($data as $row) {
			$series[] = array(
				'label' => $row[0],
				'data' => $row[1]
			);
		}

		$data = json_encode($series);

		return "\$.plot(div, {$data}, window.flot.preset.verticale);";
	}

	private function geslacht($data) {
		$series = array();
		foreach ($data as $row) {
			switch ($row[0]) {

				case 'm':
					$series[] = array(
						'label' => '',
						'data' => $row[1],
						'color' => '#AFD8F8'
					);
					break;

				case 'v':
					$series[] = array(
						'label' => '',
						'data' => $row[1],
						'color' => '#FFCBDB'
					);
					break;
			}
		}
		$data = json_encode($series);

		return "\$.plot(div, {$data}, window.flot.preset.geslacht);";
	}

	private function lichting($data) {
		$series = array();
		foreach ($data as $row) {
			$series[] = array('data' => array(array((int)$row[0], (int)$row[1])));
		}
		$data = json_encode($series);

		return "\$.plot(div, {$data}, window.flot.preset.lichting);";
	}

	private function tijd($data) {
		$series = array();
		$totaal = 0;
		foreach ($data as $tijd => $aantal) {
			$totaal += $aantal;
			$series[0][] = array($tijd, $totaal);
		}
		$data = json_encode($series);

		return "\$.plot(div, {$data}, window.flot.preset.tijd);";
	}

	/**
	 * @return string
	 * @throws CsrException
	 */
	public function getTabContent() {
		$html = '';

		foreach ($this->groep->getStatistieken() as $titel => $data) {
			$html .= '<h4>' . $titel . '</h4>';
			if (!is_array($data)) {
				$html .= $data;
				continue;
			}
			$html .= '<div id="groep-stat-' . $titel . '-' . $this->groep->id . '" class="groep-stat"></div>';
			$this->javascript .= <<<JS
var div = $("#groep-stat-{$titel}-{$this->groep->id}");
div.height(div.width());
JS;
			switch ($titel) {

				case 'Verticale':
					$this->javascript .= $this->verticale($data);
					break;

				case 'Geslacht':
					$this->javascript .= $this->geslacht($data);
					break;

				case 'Lichting':
					$this->javascript .= $this->lichting($data);
					break;

				case 'Tijd':
					$this->javascript .= $this->tijd($data);
					break;

				default:
					throw new CsrException('Onbekende statistiek soort ' . $titel);
			}
		}
		return $html;
	}

}
