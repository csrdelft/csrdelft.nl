<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * GroepStatus.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * De status van een groep of lid in een groep.
 *
 */
abstract class GroepStatus extends PersistentEnum {

	/**
	 * GroepStatus opties.
	 */
	const FT = 'ft';
	const HT = 'ht';
	const OT = 'ot';

	/**
	 * @var string[]
	 */
	protected static $supportedChoices = [
		self::FT => self::FT,
		self::HT => self::HT,
		self::OT => self::OT,
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::FT => 'Future Tempore',
		self::HT => 'Hoc Tempore',
		self::OT => 'Olim Tempore',
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToChar = [
		self::FT => 'f.t.',
		self::HT => 'h.t.',
		self::OT => 'o.t.',
	];
}
