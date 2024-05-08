<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\entity\groepen\Werkgroep;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbWerkgroep extends BbTagGroep
{
	public function getEntityClass(): string
	{
		return Werkgroep::class;
	}

	public static function getTagName()
	{
		return 'werkgroep';
	}

	public function getLidNaam(): string
	{
		return 'aanmeldingen';
	}
}
