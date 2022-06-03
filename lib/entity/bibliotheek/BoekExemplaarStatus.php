<?php


namespace CsrDelft\entity\bibliotheek;


use CsrDelft\common\Enum;

/**
 * Class BoekExemplaarStatus
 * @package CsrDelft\entity\bibliotheek
 * @method static static beschikbaar
 * @method static static uitgeleend
 * @method static static teruggegeven
 * @method static static vermist
 * @method static boolean isbeschikbaar
 * @method static boolean isuitgeleend
 * @method static boolean isteruggegeven
 * @method static boolean isvermist
 */
class BoekExemplaarStatus extends Enum
{
	const beschikbaar = 'beschikbaar';
	const uitgeleend = 'uitgeleend';
	const teruggegeven = 'teruggegeven';
	const vermist = 'vermist';

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::beschikbaar => 'Beschikbaar',
		self::uitgeleend => 'Uitgeleend',
		self::teruggegeven => 'Teruggegeven',
		self::vermist => 'Vermist'
	];

	protected static $mapChoiceToChar = [
		self::beschikbaar => "BE",
		self::uitgeleend => "UI",
		self::teruggegeven => "TE",
		self::vermist => "VE",
	];
}
