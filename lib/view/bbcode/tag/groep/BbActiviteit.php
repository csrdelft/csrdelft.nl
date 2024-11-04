<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\entity\groepen\Activiteit;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbActiviteit extends BbTagGroep
{
	/**
	 * @return string
	 *
	 * @psalm-return Activiteit::class
	 */
	public function getEntityClass(): string
	{
		return Activiteit::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'activiteit'
	 */
	public static function getTagName()
	{
		return 'activiteit';
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'aanmeldingen'
	 */
	public function getLidNaam()
	{
		return 'aanmeldingen';
	}
}
