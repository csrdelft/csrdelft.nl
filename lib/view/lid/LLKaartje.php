<?php

namespace CsrDelft\view\lid;
use CsrDelft\entity\profiel\Profiel;

/**
 * Visitekaartjes, 3 op één regel.
 */
class LLKaartje extends LLWeergave {

	public function viewHeader() {

	}

	public function viewFooter() {

	}

	public function viewLid(Profiel $profiel) {
		echo $profiel->getLink('leeg');
	}

}
