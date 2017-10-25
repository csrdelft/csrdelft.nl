<?php

namespace CsrDelft\view\groepen\leden;

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
		$this->javascript .= <<<JS

	series: {
		pie: {
			show: true,
			radius: 1,
			label: {
				show: true,
				radius: 2/3,
				formatter: function(label, series) {
					return '<div class="pie-chart-label">'+label+'<br/>'+Math.round(series.percent)+'%</div>';
				},
				threshold: 0.1
			}
		}
	},
	legend: {
		show: false
	}
JS;
		return $series;
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
		$this->javascript .= <<<JS

	series: {
		pie: {
			show: true,
			radius: 1,
			innerRadius: .5,
			label: {
				show: false
			}
		}
	},
	legend: {
		show: false
	}
JS;
		return $series;
	}

	private function lichting($data) {
		$series = array();
		foreach ($data as $row) {
			$series[] = array('data' => array(array((int)$row[0], (int)$row[1])));
		}
		$this->javascript .= <<<JS

	series: {
		bars: {
			show: true,
			barWidth: 0.5,
			align: "center",
			lineWidth: 0,
			fill: 1
		}
	},
	xaxis: {
		tickDecimals: 0
	},
	yaxis: {
		tickDecimals: 0
	}
JS;
		return $series;
	}

	private function tijd($data) {
		$series = array();
		$totaal = 0;
		foreach ($data as $tijd => $aantal) {
			$totaal += $aantal;
			$series[0][] = array($tijd, $totaal);
		}
		$this->javascript .= <<<JS

	xaxes: [{
		mode: "time"
	}],
	yaxis: {
		tickDecimals: 0
	}
JS;
		return $series;
	}

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
$.plot(div, data{$titel}{$this->groep->id}, {
JS;
			switch ($titel) {

				case 'Verticale':
					$series = $this->verticale($data);
					break;

				case 'Geslacht':
					$series = $this->geslacht($data);
					break;

				case 'Lichting':
					$series = $this->lichting($data);
					break;

				case 'Tijd':
					$series = $this->tijd($data);
					break;
			}
			// prepend data
			$data = json_encode($series);
			$this->javascript = <<<JS

var data{$titel}{$this->groep->id} = {$data};
{$this->javascript}
});
JS;
		}
		return $html;
	}

}
