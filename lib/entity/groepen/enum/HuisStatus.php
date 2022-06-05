<?php

namespace CsrDelft\entity\groepen\enum;

use CsrDelft\common\Enum;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * De status van een huis / woonoord.
 *
 * @method static static Woonoord
 * @method static static Huis
 */
class HuisStatus extends Enum
{
	/**
	 * HuisStatus opties.
	 */
	const Woonoord = 'w';
	const Huis = 'h';

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::Woonoord => 'Woonoord',
		self::Huis => 'Huis',
	];
}
