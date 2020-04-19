<?php

namespace CsrDelft\entity;

use CsrDelft\common\Enum;
use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * OntvangtContactueel.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class OntvangtContactueel extends Enum {
	/**
	 * OntvangtContactueel opties.
	 */
	const Ja = 'ja';
	const Digitaal = 'digitaal';
	const Nee = 'nee';

	public static function Nee(){
		return new static(self::Nee);
	}

	public static function Digitaal() {
		return new static(self::Digitaal);
	}

	public static function Ja() {
		return new static(self::Ja);
	}

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::Ja => 'ja',
		self::Digitaal => 'ja, digitaal',
		self::Nee => 'nee',
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToChar = [
		self::Ja => 'J',
		self::Digitaal => 'D',
		self::Nee => '-',
	];
}
