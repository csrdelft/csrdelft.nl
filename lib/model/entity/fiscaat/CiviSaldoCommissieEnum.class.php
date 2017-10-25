<?php

namespace CsrDelft\model\entity\fiscaat;

use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * CiviSaldoCommissieEnum.class.php
 *
 * Maak onderscheid tussen verschillende commissies die uit hetzelfde potje geld halen.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/04/2017
 */
class CiviSaldoCommissieEnum extends PersistentEnum {

	/**
	 * CiviSaldoCommissie opties.
	 */
	const MAALCIE = 'maalcie';
	const SOCCIE = 'soccie';
	const OWEECIE = 'oweecie';
	const ANDERS = 'anders';

	/**
	 * @var string[]
	 */
	protected static $supportedChoices = [
		self::ANDERS => self::ANDERS,
		self::SOCCIE => self::SOCCIE,
		self::OWEECIE => self::OWEECIE,
		self::MAALCIE => self::MAALCIE,
	];

	protected static $mapChoiceToDescription = [
		self::ANDERS => 'Anders',
		self::SOCCIE => 'SocCie',
		self::OWEECIE => 'OweeCie',
		self::MAALCIE => 'MaalCie',
	];
}
