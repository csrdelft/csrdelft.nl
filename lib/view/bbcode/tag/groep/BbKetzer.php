<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\entity\groepen\Ketzer;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbKetzer extends BbTagGroep
{
	/**
	 * @return string
	 *
	 * @psalm-return Ketzer::class
	 */
	public function getEntityClass(): string
	{
		return Ketzer::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'ketzer'
	 */
	public static function getTagName()
	{
		return 'ketzer';
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
