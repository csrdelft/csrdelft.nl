<?php


namespace CsrDelft\entity\bibliotheek;


use CsrDelft\common\CsrException;
use CsrDelft\Orm\Entity\PersistentEnum;

class BoekExemplaarStatus extends PersistentEnum {

	const beschikbaar = 'beschikbaar';
	const uitgeleend = 'uitgeleend';
	const teruggegeven = 'teruggegeven';
	const vermist = 'vermist';

	/**
	 * @var string[]
	 */
	protected static $supportedChoices = [
		self::beschikbaar => self::beschikbaar,
		self::uitgeleend => self::uitgeleend,
		self::teruggegeven => self::teruggegeven,
		self::vermist => self::vermist
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::beschikbaar => 'Beschikbaar',
		self::uitgeleend => 'Uitgeleend',
		self::teruggegeven => 'Teruggegeven',
		self::vermist => 'Vermist'
	];

	/**
	 * @param string $option
	 * @return string
	 * @throws CsrException
	 */
	public static function getChar($option) {
		if (isset(static::$supportedChoices[$option])) {
			return strtoupper(substr($option, 0, 2));
		} else {
			throw new CsrException('BoekExemplaarStatus onbekend');
		}
	}
}
