<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * KetzerSelectorSoort.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * De keuzesoort van een selector: AND (Multiple) / XOR (Single)
 */
abstract class KetzerSelectorSoort extends PersistentEnum {

	/**
	 * KetzerSelectorSoort opties.
	 */
	const Single = 'XOR';
	const Multiple = 'AND';

	/**
	 * @var string[]
	 */
	protected static $supportedChoices = [
		self::Single => self::Single,
		self::Multiple => self::Multiple,
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::Single => 'Keuzerondje',
		self::Multiple => 'Vinkje',
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToChar = [
		self::Multiple => 'AND',
		self::Single => 'XOR',
	];
}
