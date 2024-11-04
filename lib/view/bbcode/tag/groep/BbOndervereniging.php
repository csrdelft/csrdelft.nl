<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\entity\groepen\Ondervereniging;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbOndervereniging extends BbTagGroep
{
	/**
	 * @return string
	 *
	 * @psalm-return Ondervereniging::class
	 */
	public function getEntityClass(): string
	{
		return Ondervereniging::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'ondervereniging'
	 */
	public static function getTagName()
	{
		return 'ondervereniging';
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
