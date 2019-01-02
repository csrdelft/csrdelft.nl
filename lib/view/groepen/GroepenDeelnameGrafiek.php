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
		$series1 = htmlspecialchars(json_encode($this->series[1]));
		$series0 = htmlspecialchars(json_encode($this->series[0]));
		$step = htmlspecialchars(json_encode($this->step));

		echo <<<HTML
<div id="deelnamegrafiek">
	<div class="ctx-deelnamegrafiek" style="height: 360px;" data-series-1="{$series1}" data-series-0="{$series0}" data-step="{$step}"></div>
</div>
HTML;
	}

}
