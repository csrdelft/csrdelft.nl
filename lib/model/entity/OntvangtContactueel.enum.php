<?php

namespace CsrDelft\model\entity;

use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * OntvangtContactueel.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
abstract class OntvangtContactueel extends PersistentEnum {

	/**
	 * OntvangtContactueel opties.
	 */
	const Ja = 'ja';
	const Digitaal = 'digitaal';
	const Nee = 'nee';

	/**
	 * @var string[]
	 */
	protected static $supportedChoices = [
		self::Ja => self::Ja,
		self::Digitaal => self::Digitaal,
		self::Nee => self::Nee,
	];

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
