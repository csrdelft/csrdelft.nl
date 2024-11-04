<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\entity\groepen\Commissie;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbCommissie extends BbTagGroep
{
	/**
	 * @return string
	 *
	 * @psalm-return Commissie::class
	 */
	public function getEntityClass(): string
	{
		return Commissie::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'commissie'
	 */
	public static function getTagName()
	{
		return 'commissie';
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'leden'
	 */
	public function getLidNaam()
	{
		return 'leden';
	}
}
