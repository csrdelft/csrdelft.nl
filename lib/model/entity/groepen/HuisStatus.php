<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * HuisStatus.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * De status van een huis / woonoord.
 */
abstract class HuisStatus extends PersistentEnum {

	/**
	 * HuisStatus opties.
	 */
	const Woonoord = 'w';
	const Huis = 'h';

	/**
	 * @var string[]
	 */
	protected static $supportedChoices = [
		self::Woonoord => self::Woonoord,
		self::Huis => self::Huis,
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::Woonoord => 'Woonoord',
		self::Huis => 'Huis',
	];
}
