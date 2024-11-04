<?php

namespace CsrDelft\model\entity;

use CsrDelft\common\Enum;

/**
 * LidStatus.enum.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class LidStatus extends Enum
{
	/**
	 * Status voor h.t. leden.
	 */
	const Noviet = 'S_NOVIET';
	const Lid = 'S_LID';
	const Gastlid = 'S_GASTLID';

	/**
	 * Status voor o.t. leden.
	 */
	const Oudlid = 'S_OUDLID';
	const Erelid = 'S_ERELID';

	/**
	 * Status voor niet-leden.
	 */
	const Overleden = 'S_OVERLEDEN';
	const Exlid = 'S_EXLID';
	const Nobody = 'S_NOBODY';
	const Commissie = 'S_CIE';
	const Kringel = 'S_KRINGEL';

	/**
	 * @var string[]
	 */
	protected static $lidlike = [
		self::Noviet => self::Noviet,
		self::Lid => self::Lid,
		self::Gastlid => self::Gastlid,
	];

	/**
	 * @var string[]
	 */
	protected static $oudlidlike = [
		self::Oudlid => self::Oudlid,
		self::Erelid => self::Erelid,
	];

	/**
	 * @var string[]
	 */
	protected static $fiscaalOudlidlike = [
		self::Oudlid => self::Oudlid,
		self::Erelid => self::Erelid,
		self::Exlid => self::Exlid,
		self::Nobody => self::Nobody,
	];

	/**
	 * @var string[]
	 */
	protected static $fiscaalLidlike = [
		self::Noviet => self::Noviet,
		self::Lid => self::Lid,
		self::Gastlid => self::Gastlid,
		self::Kringel => self::Kringel,
	];

	protected static $zoekenLidlike = [
		self::Noviet => self::Noviet,
		self::Lid => self::Lid,
		self::Gastlid => self::Gastlid,
		self::Kringel => self::Kringel,
	];

	/**
	 * @var string[]
	 */
	protected static $zoekenOudlidlike = [
		self::Oudlid => self::Oudlid,
		self::Erelid => self::Erelid,
	];

	protected static $zoekenExlidlike = [
		self::Nobody => self::Nobody,
		self::Exlid => self::Exlid,
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::Noviet => 'Noviet',
		self::Lid => 'Lid',
		self::Gastlid => 'Gastlid',
		self::Oudlid => 'Oudlid',
		self::Erelid => 'Erelid',
		self::Overleden => 'Overleden',
		self::Exlid => 'Ex-lid',
		self::Nobody => 'Nobody',
		self::Commissie => 'Commissie (LDAP)',
		self::Kringel => 'Kringel',
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToChar = [
		self::Noviet => '',
		self::Lid => '',
		self::Gastlid => '',
		self::Commissie => '∈',
		self::Exlid => '∉',
		self::Nobody => '∉',
		self::Kringel => '~',
		self::Oudlid => '•',
		self::Erelid => '☀',
		self::Overleden => '✝',
	];

	/**
	 * @return string[]
	 *
	 * @psalm-return list<string>
	 */
	public static function getLidLike(): array
	{
		return array_values(static::$lidlike);
	}

	/**
	 * @return static[]
	 *
	 * @psalm-return array<static>
	 */
	public static function getLidLikeObject(): array
	{
		return array_map(fn($val) => static::from($val), static::getLidLike());
	}

	/**
	 * @return string[]
	 *
	 * @psalm-return list<string>
	 */
	public static function getOudlidLike(): array
	{
		return array_values(static::$oudlidlike);
	}

	/**
	 * @return static[]
	 *
	 * @psalm-return array<static>
	 */
	public static function getOudLidLikeObject(): array
	{
		return array_map(fn($val) => static::from($val), static::getOudLidLike());
	}

	/**
	 * @param string $option
	 *
	 * @return bool
	 */
	public static function isLidLike($option)
	{
		return isset(static::$lidlike[$option]);
	}

	/**
	 * @param string $option
	 *
	 * @return bool
	 */
	public static function isOudlidLike($option)
	{
		return isset(static::$oudlidlike[$option]);
	}

	/**
	 * @return value-of<TArray>[]
	 *
	 * @psalm-return list<value-of<array>>
	 */
	public static function getZoekenLidLike(): array
	{
		return array_values(static::$zoekenLidlike);
	}

	/**
	 * @return string[]
	 *
	 * @psalm-return list<string>
	 */
	public static function getZoekenOudlidLike(): array
	{
		return array_values(static::$zoekenOudlidlike);
	}

	/**
	 * @return value-of<TArray>[]
	 *
	 * @psalm-return list<value-of<array>>
	 */
	public static function getZoekenExlidLike(): array
	{
		return array_values(static::$zoekenExlidlike);
	}

	public function getChar(): string
	{
		return static::$mapChoiceToChar[$this->getValue()];
	}
}
