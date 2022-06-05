<?php

namespace CsrDelft\entity;

use CsrDelft\common\Enum;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @method static Geslacht Man()
 * @method static Geslacht Vrouw()
 * @method static boolean isMan($geslacht)
 * @method static boolean isVrouw($geslacht)
 */
class Geslacht extends Enum
{
	/**
	 * Geslacht opties.
	 */
	const Man = 'm';
	const Vrouw = 'v';

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::Man => 'man',
		self::Vrouw => 'vrouw',
	];
}
