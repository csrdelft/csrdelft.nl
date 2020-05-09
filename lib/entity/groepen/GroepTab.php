<?php

namespace CsrDelft\entity\groepen;


use CsrDelft\common\Enum;

/**
 * GroepTab.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * De verschillende tabbladen om een groep weer te geven.
 *
 */
class GroepTab extends Enum {

	/**
	 * GroepTab opties.
	 */
	const Lijst = 'lijst';
	const Pasfotos = 'pasfotos';
	const Statistiek = 'stats';
	const Emails = 'emails';
	const Eetwens = 'eetwens';

	public static function Lijst() {
		return static::from(self::Lijst);
	}

	public static function Pasfotos() {
		return static::from(self::Pasfotos);
	}

	public static function Statistiek() {
		return static::from(self::Statistiek);
	}

	public static function Emails() {
		return static::from(self::Emails);
	}

	public static function Eetwens() {
		return static::from(self::Eetwens);
	}

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::Lijst => 'Lijst',
		self::Pasfotos => 'Pasfoto\'s',
		self::Statistiek => 'Statistiek',
		self::Emails => 'E-mails',
		self::Eetwens => 'Allergie/dieet',
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToChar = [
		self::Lijst => 'l',
		self::Pasfotos => 'p',
		self::Statistiek => 's',
		self::Emails => 'e',
		self::Eetwens => 'a',
	];
}
