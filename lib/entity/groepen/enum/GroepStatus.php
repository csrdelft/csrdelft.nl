<?php

namespace CsrDelft\entity\groepen\enum;

use CsrDelft\common\Enum;

/**
 * GroepStatus.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * De status van een groep of lid in een groep.
 *
 * @method static static FT
 * @method static static HT
 * @method static static OT
 * @method static boolean isFT($groepStatus)
 * @method static boolean isHT($groepStatus)
 * @method static boolean isOT($groepStatus)
 */
class GroepStatus extends Enum {
	/**
	 * GroepStatus opties.
	 */
	const FT = 'ft';
	const HT = 'ht';
	const OT = 'ot';

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
