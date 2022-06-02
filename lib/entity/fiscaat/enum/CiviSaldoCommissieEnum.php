<?php

namespace CsrDelft\entity\fiscaat\enum;

use CsrDelft\common\Enum;

/**
 * Maak onderscheid tussen verschillende commissies die uit hetzelfde potje geld halen.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 07/04/2017
 */
class CiviSaldoCommissieEnum extends Enum
{
	/**
	 * CiviSaldoCommissie opties.
	 */
	const MAALCIE = 'maalcie';
	const SOCCIE = 'soccie';
	const OWEECIE = 'oweecie';
	const ANDERS = 'anders';

	protected static $mapChoiceToDescription = [
		self::ANDERS => 'Anders',
		self::SOCCIE => 'SocCie',
		self::OWEECIE => 'OweeCie',
		self::MAALCIE => 'MaalCie',
	];
}
