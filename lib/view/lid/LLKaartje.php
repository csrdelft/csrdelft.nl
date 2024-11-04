<?php

namespace CsrDelft\view\lid;
use CsrDelft\entity\profiel\Profiel;

/**
 * Visitekaartjes, 3 op één regel.
 */
class LLKaartje extends LLWeergave
{
	/**
	 * @return string
	 *
	 * @psalm-return ''
	 */
	public function viewHeader()
	{
		return '';
	}

	/**
	 * @return string
	 *
	 * @psalm-return ''
	 */
	public function viewFooter()
	{
		return '';
	}

	/**
	 * @return string
	 */
	public function viewLid(Profiel $profiel)
	{
		return $profiel->getLink('leeg');
	}
}
