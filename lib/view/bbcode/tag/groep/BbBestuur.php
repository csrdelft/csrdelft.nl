<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\entity\groepen\Bestuur;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbBestuur extends BbTagGroep
{
	public function getEntityClass(): string
	{
		return Bestuur::class;
	}

	public static function getTagName()
	{
		return 'bestuur';
	}

	public function getLidNaam(): string
	{
		return 'personen';
	}
}
