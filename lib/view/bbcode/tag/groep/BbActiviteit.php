<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\entity\groepen\Activiteit;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbActiviteit extends BbTagGroep
{
	public function getEntityClass(): string
	{
		return Activiteit::class;
	}

	public static function getTagName(): string
	{
		return 'activiteit';
	}

	public function getLidNaam(): string
	{
		return 'aanmeldingen';
	}
}
