<?php

namespace CsrDelft\model\entity;

use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * Geslacht.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
abstract class Geslacht extends PersistentEnum {

	/**
	 * Geslacht opties.
	 */
	const Man = 'm';
	const Vrouw = 'v';

	/**
	 * @var string[]
	 */
	protected static $supportedChoices = [
		self::Man => self::Man,
		self::Vrouw => self::Vrouw,
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::Man => 'man',
		self::Vrouw => 'vrouw',
	];
}
