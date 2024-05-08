<?php

namespace CsrDelft\entity;

use CsrDelft\common\Enum;

/**
 * OntvangtContactueel.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class OntvangtContactueel extends Enum
{
	/**
	 * OntvangtContactueel opties.
	 */
	const Ja = 'ja';
	const Digitaal = 'digitaal';
	const Nee = 'nee';

	public static function Nee(): OntvangtContactueel
	{
		return static::from(self::Nee);
	}

	public static function Digitaal(): OntvangtContactueel
	{
		return static::from(self::Digitaal);
	}

	public static function Ja(): OntvangtContactueel
	{
		return static::from(self::Ja);
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
