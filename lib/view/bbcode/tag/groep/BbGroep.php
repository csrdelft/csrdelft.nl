<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\entity\groepen\RechtenGroep;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbGroep extends BbTagGroep
{
	public function getEntityClass(): string
	{
		return RechtenGroep::class;
	}

	public static function getTagName()
	{
		return 'groep';
	}

	public function getLidNaam(): string
	{
		return 'personen';
	}
}
