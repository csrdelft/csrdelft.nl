<?php

namespace CsrDelft\view\lid;

use CsrDelft\entity\profiel\Profiel;
use CsrDelft\service\LidZoekerService;

abstract class LLWeergave {

	protected $leden;
	public $velden;
	public function __construct(LidZoekerService $zoeker) {
		$this->leden = $zoeker->getLeden();
		$this->velden = $zoeker->getVelden();
	}

	public abstract function viewHeader();

	public abstract function viewFooter();

	//viewLid print één regel of vakje ofzo.
	public abstract function viewLid(Profiel $profiel);

	public function view() {
		$this->viewHeader();
		foreach ($this->leden as $lid) {
			$this->viewLid($lid);
		}
		$this->viewFooter();
	}

}
