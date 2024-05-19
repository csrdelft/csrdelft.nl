<?php

namespace CsrDelft\view\lid;

use CsrDelft\entity\profiel\Profiel;
use CsrDelft\service\LidZoekerService;

abstract class LLWeergave
{
	protected $leden;
	public $velden;
	public function __construct(LidZoekerService $zoeker)
	{
		$this->leden = $zoeker->getLeden();
		$this->velden = $zoeker->getVelden();
	}

	abstract public function viewHeader();

	abstract public function viewFooter();

	//viewLid print één regel of vakje ofzo.
	abstract public function viewLid(Profiel $profiel);

	public function __toString(): string
	{
		$html = '';
		$html .= $this->viewHeader();
		foreach ($this->leden as $lid) {
			$html .= $this->viewLid($lid);
		}
		$html .= $this->viewFooter();
		return $html;
	}
}
