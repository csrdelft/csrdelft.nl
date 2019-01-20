<?php

namespace CsrDelft\view\groepen;

use CsrDelft\model\entity\Geslacht;
use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\View;

class GroepenDeelnameGrafiek implements View {

	private $series = array();
	private $step = array();

	private $mannen = [];
	private $vrouwen = [];

	/**
	 * GroepenDeelnameGrafiek constructor.
	 * @param AbstractGroep[] $groepen
	 */
	public function __construct($groepen) {
		foreach ($groepen as $groep) {
			$mannen = 0;
			$vrouwen = 0;

			var_dump($groep->getLeden());

			foreach ($groep->getLeden() as $lid) {
				$profiel = ProfielModel::get($lid->uid);
				if ($profiel->geslacht === Geslacht::Man) {
					$mannen += 1;
				} else {
					$vrouwen += 1;
				}
			}

			$this->series[] = [
				"moment" => strtotime($groep->begin_moment) * 1000,
				"aantalMannen" => $mannen,
				"aantalVrouwen" => $vrouwen,
				"naam" => $groep->naam,
			];
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
		$step = htmlspecialchars(json_encode($this->step));

		$series = htmlspecialchars(json_encode($this->series));

		echo <<<HTML
<div id="deelnamegrafiek">
	<div class="ctx-deelnamegrafiek" style="height: 360px;width:100%;" data-series="{$series}" data-step="{$step}"></svg>
</div>
HTML;
	}

}
