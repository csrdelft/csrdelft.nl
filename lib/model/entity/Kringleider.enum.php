<?php

namespace CsrDelft\model\entity;

use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * Kringleider.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
abstract class Kringleider extends PersistentEnum {

	/**
	 * Kringleider opties.
	 */
	const Ouderejaars = 'o';
	const Eerstejaars = 'e';
	const Nee = 'n';

	/**
	 * @var string[]
	 */
	protected static $supportedChoices = [
		self::Ouderejaars => self::Ouderejaars,
		self::Eerstejaars => self::Eerstejaars,
		self::Nee => self::Nee,
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::Ouderejaars => 'Ouderejaarskring',
		self::Eerstejaars => 'Eerstejaarskring',
		self::Nee => 'Nee',
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToChar = [
		self::Ouderejaars => 'O',
		self::Eerstejaars => 'E',
		self::Nee => '-',
	];
}
