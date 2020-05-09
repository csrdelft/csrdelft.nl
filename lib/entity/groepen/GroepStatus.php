<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\Enum;

/**
 * GroepStatus.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * De status van een groep of lid in een groep.
 *
 */
class GroepStatus extends Enum {
	/**
	 * GroepStatus opties.
	 */
	const FT = 'ft';
	const HT = 'ht';
	const OT = 'ot';

	public static function FT() {
		return static::from(self::FT);
	}

	public static function HT() {
		return static::from(self::HT);
	}

	public static function OT() {
		return static::from(self::OT);
	}

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::FT => 'Future Tempore',
		self::HT => 'Hoc Tempore',
		self::OT => 'Olim Tempore',
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToChar = [
		self::FT => 'f.t.',
		self::HT => 'h.t.',
		self::OT => 'o.t.',
	];
}
