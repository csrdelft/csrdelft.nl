<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\entity\groepen\Commissie;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbCommissie extends BbTagGroep
{
	public function getEntityClass(): string
	{
		return Commissie::class;
	}

	public static function getTagName()
	{
		return 'commissie';
	}

	public function getLidNaam(): string
	{
		return 'leden';
	}
}
