<?php

namespace CsrDelft\entity\forum;

use CsrDelft\common\Enum;

class ForumDraadMeldingNiveau extends Enum {

	const NOOIT = 'nooit';
	const VERMELDING = 'vermelding';
	const ALTIJD = 'altijd';

	public static function NOOIT() {
		return static::from(self::NOOIT);
	}

	public static function VERMELDING() {
		return static::from(self::VERMELDING);
	}

	public static function ALTIJD() {
		return static::from(self::ALTIJD);
	}

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::NOOIT => 'Nooit',
		self::VERMELDING => 'Bij vermelding',
		self::ALTIJD => 'Altijd'
	];
}
