<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\Enum;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/04/2019
 */
class GroepVersie extends Enum {
	const V1 = 'v1';
	const V2 = 'v2';

	public static function V1() {
		return static::from(self::V1);
	}

	public static function V2() {
		return static::from(self::V2);
	}

	protected static $supportedChoices = [
		self::V1 => self::V1,
		self::V2 => self::V2,
	];

	protected static $mapChoiceToDescription = [
		self::V1 => 'Versie 1',
		self::V2 => 'Versie 2',
	];
}
