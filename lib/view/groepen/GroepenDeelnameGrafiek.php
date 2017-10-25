<?php

namespace CsrDelft\view\groepen;

use CsrDelft\model\entity\Geslacht;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\View;

class GroepenDeelnameGrafiek implements View {

	private $series = array();
	private $step = array();

	public function __construct($groepen) {
		$mannen = array();
		$vrouwen = array();
		$smallest_diff = PHP_INT_MAX;
		$previous_time = 0;
		foreach ($groepen as $groep) {
			$time = strtotime($groep->begin_moment);
			$smallest_diff = min($smallest_diff, abs($time - $previous_time));
			$previous_time = $time;
			if (!isset($mannen[$time])) {
				$mannen[$time] = 0;
				$vrouwen[$time] = 0;
			}
			foreach ($groep->getLeden() as $lid) {
				$profiel = ProfielModel::get($lid->uid);
				if ($profiel->geslacht === Geslacht::Man) {
					$mannen[$time] += 1;
				} else {
					$vrouwen[$time] += 1;
				}
			}
		}
		$year = 365 * 24 * 60 * 60;
		$month = 30 * 24 * 60 * 60;
		$day = 24 * 60 * 60;
		if ($smallest_diff >= $year) {
			$this->step[] = floor($smallest_diff / $year);
			$this->step[] = 'year';
		} elseif ($smallest_diff >= $month) {
			$this->step[] = floor($smallest_diff / $month);
			$this->step[] = 'month';
		} else {
			$this->step[] = floor($smallest_diff / $day);
			$this->step[] = 'day';
		}
		foreach ($mannen as $time => $aantal) {
			$this->series[0][] = array($time * 1000, $aantal);
			$this->series[1][] = array($time * 1000, $vrouwen[$time]);
		}
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getModel() {
		return $this->series;
	}

	public function getTitel() {
		return null;
	}

	public function view() {
		?>
		<div id="deelnamegrafiek" style="height: 360px;">
			<script type="text/javascript">
				$(document).ready(function () {
					var series = [{
						data: <?= json_encode($this->series[1]); ?>,
						label: "",
						color: "#FFCBDB"
					}, {
						data: <?= json_encode($this->series[0]); ?>,
						label: "",
						color: "#AFD8F8"
					}
					];
					var options = {
						series: {
							bars: {
								show: true,
								lineWidth: 20
							},
							stack: true
						}, yaxis: {
							tickDecimals: 0
						},
						xaxis: {
							autoscaleMargin: .01
						},
						xaxes: [{
							mode: "time",
							minTickSize: <?= json_encode($this->step); ?>
						}]
					};
					$.plot("#deelnamegrafiek", series, options);
				});
			</script>
		</div>
		<?php
	}

}
