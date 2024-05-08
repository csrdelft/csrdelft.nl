<?php

namespace CsrDelft\view\groepen;

use CsrDelft\entity\Geslacht;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\interfaces\HeeftMoment;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\ToResponse;
use CsrDelft\view\View;
use Symfony\Component\HttpFoundation\Response;

class GroepenDeelnameGrafiek implements View, ToResponse
{
	private $series = [];
	private $step = [];

	/**
	 * GroepenDeelnameGrafiek constructor.
	 * @param Groep[] $groepen
	 */
	public function __construct($groepen)
	{
		$aantalMannen = [];
		$aantalVrouwen = [];
		$groepNamen = [];
		$groepJaren = [];
		$index = 0;
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

			$aantalMannen[] = $mannen;
			$aantalVrouwen[] = $vrouwen;
			$groepNamen[] = $groep->naam;

			if ($groep instanceof HeeftMoment) {
				$groepJaren[] = $groep->getBeginMoment()->format('Y');
			} else {
				$groepJaren[] = '000' . $index++;
			}
		}
		$this->series = [
			'labels' => $groepNamen,
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
				],
			],
		];
	}

	public function getBreadcrumbs(): null
	{
		return null;
	}

	public function getModel()
	{
		return $this->series;
	}

	public function getTitel(): null
	{
		return null;
	}

	public function getHtml(): string
	{
		$step = htmlspecialchars(json_encode($this->step));

		$series = htmlspecialchars(json_encode($this->series));

		return <<<HTML
<div id="deelnamegrafiek">
	<div class="ctx-deelnamegrafiek" style="height: 360px;width:100%;" data-data="{$series}" data-step="{$step}"></svg>
</div>
HTML;
	}

	public function __toString()
	{
		return $this->getHtml();
	}

	public function toResponse(): Response
	{
		return new Response($this->getHtml());
	}
}
