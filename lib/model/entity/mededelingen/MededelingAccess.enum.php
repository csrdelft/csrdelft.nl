<?php

namespace CsrDelft\model\entity\mededelingen;

use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * MededelingAccess.enum.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
abstract class MededelingAccess extends PersistentEnum {

	/**
	 * MededelingAccess opties.
	 */
	const Post = 'P_NEWS_POST';
	const Mod = 'P_NEWS_MOD';

	/**
	 * @var string[]
	 */
	protected static $supportedChoices = [
		self::Post => self::Post,
		self::Mod => self::Mod,
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::Post => 'Mededelingen aanmaken',
		self::Mod => 'Mededelingen bewerken',
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToChar = [
		self::Post => 'P',
		self::Mod => 'M',
	];
}
