<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/04/2019
 */
class GroepVersie extends PersistentEnum {
	const V1 = 'v1';
	const V2 = 'v2';

	protected static $mapChoiceToChar = [
		self::V1 => '1',
		self::V2 => '2',
	];

	protected static $supportedChoices = [
		self::V1 => self::V1,
		self::V2 => self::V2,
	];

	protected static $mapChoiceToDescription = [
		self::V1 => 'Versie 1',
		self::V2 => 'Versie 2',
	];
}
