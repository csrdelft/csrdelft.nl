<?php

namespace CsrDelft\view\groepen;

use CsrDelft\entity\Geslacht;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\ToResponse;
use CsrDelft\view\View;
use Symfony\Component\HttpFoundation\Response;

class GroepenDeelnameGrafiek implements View, ToResponse {

	private $series = array();
	private $step = array();

	/**
	 * GroepenDeelnameGrafiek constructor.
	 * @param Groep[] $groepen
	 */
	public function __construct($groepen) {
		$aantalMannen = [];
		$aantalVrouwen = [];
		$groepNamen = [];
		$groepJaren = [];
		foreach ($groepen as $groep) {
			$mannen = 0;
			$vrouwen = 0;

			foreach ($groep->getLeden() as $lid) {
				$profiel = ProfielRepository::get($lid->uid);
				if ($profiel->geslacht->getValue() === Geslacht::Man) {
					$mannen += 1;
				} else {
					$vrouwen += 1;
				}
			}

			$this->series[] = [
				"moment" => $groep->beginMoment->getTimestamp() * 1000,
				"aantalMannen" => $mannen,
				"aantalVrouwen" => $vrouwen,
				"naam" => $groep->naam,
			];

			$aantalMannen[] = $mannen;
			$aantalVrouwen[] = $vrouwen;
			$groepNamen[] = $groep->naam;
			$groepJaren[] = $groep->beginMoment->format('Y');
		}
		$this->series = [
			'labels'=> $groepNamen,
			'jaren' => $groepJaren,
			'datasets' => [
				[
					'label' => 'Aantal mannen',
					'data' => $aantalMannen,
					'backgroundColor' => '#AFD8F8',
				],
				[
					'label' => 'Aantal vrouwen',
					'data' => $aantalVrouwen,
					'backgroundColor' => '#FFCBDB',
				]
			]
		];
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

	public function getHtml() {
		$step = htmlspecialchars(json_encode($this->step));

		$series = htmlspecialchars(json_encode($this->series));

		return <<<HTML
<div id="deelnamegrafiek">
	<div class="ctx-deelnamegrafiek" style="height: 360px;width:100%;" data-data="{$series}" data-step="{$step}"></svg>
</div>
HTML;
	}

	public function __toString() {
		return $this->getHtml();
	}

	public function toResponse(): Response {
		return new Response($this->getHtml());
	}
}
