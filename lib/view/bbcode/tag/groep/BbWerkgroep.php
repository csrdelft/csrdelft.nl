<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\entity\groepen\Werkgroep;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbWerkgroep extends BbTagGroep
{
	/**
	 * @return string
	 *
	 * @psalm-return Werkgroep::class
	 */
	public function getEntityClass(): string
	{
		return Werkgroep::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'werkgroep'
	 */
	public static function getTagName()
	{
		return 'werkgroep';
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'aanmeldingen'
	 */
	public function getLidNaam()
	{
		return 'aanmeldingen';
	}
}
