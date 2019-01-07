<?php

namespace CsrDelft\model\entity\forum;

use CsrDelft\common\CsrException;
use CsrDelft\Orm\Entity\PersistentEnum;

class ForumDraadMeldingNiveau extends PersistentEnum {

	const nooit = 'nooit';
	const vermelding = 'vermelding';
	const altijd = 'altijd';

	/**
	 * @var string[]
	 */
	protected static $supportedChoices = [
		self::nooit => self::nooit,
		self::vermelding => self::vermelding,
		self::altijd => self::altijd
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::nooit => 'Nooit',
		self::vermelding => 'Bij vermelding',
		self::altijd => 'Altijd'
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

	public static function isOptie($optie) {
		return isset(static::$supportedChoices[$optie]);
	}
}