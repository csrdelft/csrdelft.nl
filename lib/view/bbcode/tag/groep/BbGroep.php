<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\entity\groepen\RechtenGroep;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbGroep extends BbTagGroep
{
	/**
	 * @return string
	 *
	 * @psalm-return RechtenGroep::class
	 */
	public function getEntityClass(): string
	{
		return RechtenGroep::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'groep'
	 */
	public static function getTagName()
	{
		return 'groep';
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'personen'
	 */
	public function getLidNaam()
	{
		return 'personen';
	}
}
