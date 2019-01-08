<?php

namespace CsrDelft\model\entity\forum;

use CsrDelft\common\CsrException;
use CsrDelft\Orm\Entity\PersistentEnum;

class ForumDraadMeldingNiveau extends PersistentEnum {

	const NOOIT = 'nooit';
	const VERMELDING = 'vermelding';
	const ALTIJD = 'altijd';

	/**
	 * @var string[]
	 */
	protected static $supportedChoices = [
		self::NOOIT => self::NOOIT,
		self::VERMELDING => self::VERMELDING,
		self::ALTIJD => self::ALTIJD
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::NOOIT => 'Nooit',
		self::VERMELDING => 'Bij vermelding',
		self::ALTIJD => 'Altijd'
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
			throw new CsrException('ForumDraadMeldingNiveau onbekend');
		}
	}

	public static function isOptie($optie) {
		return isset(static::$supportedChoices[$optie]);
	}
}