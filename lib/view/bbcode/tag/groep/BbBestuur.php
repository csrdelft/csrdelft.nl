<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\entity\groepen\Bestuur;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbBestuur extends BbTagGroep
{
	/**
	 * @return string
	 *
	 * @psalm-return Bestuur::class
	 */
	public function getEntityClass(): string
	{
		return Bestuur::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'bestuur'
	 */
	public static function getTagName()
	{
		return 'bestuur';
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
