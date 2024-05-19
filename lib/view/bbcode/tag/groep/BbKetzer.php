<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\entity\groepen\Ketzer;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbKetzer extends BbTagGroep
{
	public function getEntityClass(): string
	{
		return Ketzer::class;
	}

	public static function getTagName()
	{
		return 'ketzer';
	}

	public function getLidNaam(): string
	{
		return 'aanmeldingen';
	}
}
