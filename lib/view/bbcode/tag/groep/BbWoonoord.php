<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\entity\groepen\Woonoord;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbWoonoord extends BbTagGroep
{
	/**
	 * @return string
	 *
	 * @psalm-return Woonoord::class
	 */
	public function getEntityClass(): string
	{
		return Woonoord::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'woonoord'
	 */
	public static function getTagName()
	{
		return 'woonoord';
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'bewoners'
	 */
	public function getLidNaam()
	{
		return 'bewoners';
	}
}
