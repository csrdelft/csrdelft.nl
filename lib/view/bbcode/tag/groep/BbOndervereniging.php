<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\entity\groepen\Ondervereniging;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbOndervereniging extends BbTagGroep
{
	public function getEntityClass(): string
	{
		return Ondervereniging::class;
	}

	public static function getTagName()
	{
		return 'ondervereniging';
	}

	public function getLidNaam()
	{
		return 'leden';
	}
}
