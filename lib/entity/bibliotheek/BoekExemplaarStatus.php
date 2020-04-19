<?php


namespace CsrDelft\entity\bibliotheek;


use CsrDelft\common\CsrException;
use CsrDelft\common\Enum;
use CsrDelft\Orm\Entity\PersistentEnum;

class BoekExemplaarStatus extends Enum {

	const beschikbaar = 'beschikbaar';
	const uitgeleend = 'uitgeleend';
	const teruggegeven = 'teruggegeven';
	const vermist = 'vermist';

	public static function beschikbaar() {
		return static::from(self::beschikbaar);
	}

	public static function uitgeleend() {
		return static::from(self::uitgeleend);
	}

	public static function teruggegeven() {
		return static::from(self::teruggegeven);
	}

	public static function vermist() {
		return static::from(self::vermist);
	}

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::beschikbaar => 'Beschikbaar',
		self::uitgeleend => 'Uitgeleend',
		self::teruggegeven => 'Teruggegeven',
		self::vermist => 'Vermist'
	];

	protected static $mapChoiceToChar = [
		self::beschikbaar => "BE",
		self::uitgeleend => "UI",
		self::teruggegeven => "TE",
		self::vermist => "VE",
	];
}
