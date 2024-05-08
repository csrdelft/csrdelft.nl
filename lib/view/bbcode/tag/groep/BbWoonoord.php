<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\entity\groepen\Woonoord;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbWoonoord extends BbTagGroep
{
	public function getEntityClass(): string
	{
		return Woonoord::class;
	}

	public static function getTagName(): string
	{
		return 'woonoord';
	}

	public function getLidNaam(): string
	{
		return 'bewoners';
	}
}
