<?php

namespace CsrDelft\entity;

use CsrDelft\common\Enum;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class Geslacht extends Enum {
	/**
	 * Geslacht opties.
	 */
	const Man = 'm';
	const Vrouw = 'v';

	public static function Man() {
		return static::from(self::Man);
	}

	public static function Vrouw() {
		return static::from(self::Vrouw);
	}

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::Man => 'man',
		self::Vrouw => 'vrouw',
	];
}
