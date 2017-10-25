<?php

namespace CsrDelft\model\entity\fiscaat;

use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * SaldoCommissie.enum.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
abstract class SaldoCommissie extends PersistentEnum {

	/**
	 * SaldoCommissie opties.
	 */
	const SocCie = 'soccie';
	const MaalCie = 'maalcie';

	/**
	 * @var string[]
	 */
	protected static $supportedChoices = [
		self::SocCie => self::SocCie,
		self::MaalCie => self::MaalCie,
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::SocCie => 'SocCie',
		self::MaalCie => 'MaalCie',
	];
}
