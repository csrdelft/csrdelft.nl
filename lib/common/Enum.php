<?php

namespace CsrDelft\common;

use InvalidArgumentException;
use ReflectionClass;

/**
 * Enum implementatie.
 *
 * Gebruik static::ENUM() om een instance te krijgen van de enum,
 *
 * Gebruik static::isENUM($enum) om te controleren of een enum van een bepaald type is.
 *
 * Je kan er helaas niet vanuit gaan dat twee instances met dezelfde waarde van een enum hetzelfde zijn.
 * Gebruik dus == of ::isENUM($enum) om de waarde te checken.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 2020-08-16
 */
abstract class Enum
{
	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [];
	private static $constCacheArray = null;
	private static $instanceCacheArray = [];
	private $value;

	/**
	 * Gebruik de from methode om een enum te maken.
	 * Enum constructor.
	 * @param $value
	 */
	private function __construct($value)
	{
		if (!static::isValidValue($value)) {
			throw new InvalidArgumentException(
				'Invalid enum value: ' . $value . ' in ' . static::class::class
			);
		}
		$this->value = $value;
	}

	public static function isValidValue(string $value): bool
	{
		$values = array_values(self::getConstants());
		return in_array($value, $values, $strict = true);
	}

	private static function getConstants()
	{
		if (self::$constCacheArray == null) {
			self::$constCacheArray = [];
		}
		if (!array_key_exists(static::class, self::$constCacheArray)) {
			$reflect = new ReflectionClass(static::class);
			self::$constCacheArray[static::class] = $reflect->getConstants();
		}
		return self::$constCacheArray[static::class];
	}

	/**
	 * @return value-of<TArray>[]
	 *
	 * @psalm-return list<value-of<array>>
	 */
	public static function getEnumValues(): array
	{
		return array_values(self::getConstants());
	}

	/**
	 * @return string[]
	 *
	 * @psalm-return array<string>
	 */
	public static function getEnumDescriptions(): array
	{
		return static::$mapChoiceToDescription;
	}

	/**
	 * @param null|string $value
	 *
	 * @return static
	 */
	public static function from(string|null $value)
	{
		if (!static::isValidValue($value)) {
			throw new InvalidArgumentException(
				'Invalid enum value: ' . $value . ' in ' . static::class
			);
		}

		if (!isset(self::$instanceCacheArray[static::class])) {
			self::$instanceCacheArray[static::class] = [];
		}

		if (!isset(self::$instanceCacheArray[static::class][$value])) {
			self::$instanceCacheArray[static::class][$value] = new static($value);
		}

		return self::$instanceCacheArray[static::class][$value];
	}

	public function getValue()
	{
		return $this->value;
	}

	public function getDescription(): string
	{
		return static::$mapChoiceToDescription[$this->value];
	}

	/**
	 * @return static[]
	 *
	 * @psalm-return array<static>
	 */
	public static function all(): array
	{
		return array_map(['static', 'from'], static::getEnumValues());
	}
}
