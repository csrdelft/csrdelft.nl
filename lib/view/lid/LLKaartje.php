<?php

namespace CsrDelft\view\lid;
use CsrDelft\entity\profiel\Profiel;

/**
 * Visitekaartjes, 3 op één regel.
 */
class LLKaartje extends LLWeergave
{
	public function viewHeader(): string
	{
		return '';
	}

	public function viewFooter(): string
	{
		return '';
	}

	public function viewLid(Profiel $profiel)
	{
		return $profiel->getLink('leeg');
	}
}
