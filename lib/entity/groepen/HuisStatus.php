<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\Enum;

/**
 * HuisStatus.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * De status van een huis / woonoord.
 */
class HuisStatus extends Enum {

	/**
	 * HuisStatus opties.
	 */
	const Woonoord = 'w';
	const Huis = 'h';

	public static function Woonoord() {
		return static::from(self::Woonoord);
	}

	public static function Huis() {
		return static::from(self::Huis);
	}

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::Woonoord => 'Woonoord',
		self::Huis => 'Huis',
	];
}
