<?php

namespace CsrDelft\model\entity;

use CsrDelft\common\Enum;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class Kringleider extends Enum
{

	/**
	 * Kringleider opties.
	 */
	const Ouderejaars = 'o';
	const Eerstejaars = 'e';
	const Nee = 'n';

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::Ouderejaars => 'Ouderejaarskring',
		self::Eerstejaars => 'Eerstejaarskring',
		self::Nee => 'Nee',
	];
}
