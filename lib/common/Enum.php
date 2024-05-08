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
				'Invalid enum value: ' . $value . ' in ' . get_class(static::class)
			);
		}
		$this->value = $value;
	}

	public static function isValidValue($value)
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

	public static function getEnumValues()
	{
		return array_values(self::getConstants());
	}

	public static function getEnumDescriptions()
	{
		return static::$mapChoiceToDescription;
	}

	/**
	 * Returns a value when called statically like so: MyEnum::SOME_VALUE() given SOME_VALUE is a class constant
	 * Returns if a value is part of this enum if called like MyEnum::isSOME_VALUE()
	 *
	 * @param string $name
	 * @param array $arguments
	 *
	 * @return static|bool
	 * @psalm-pure
	 * @throws \BadMethodCallException
	 */
	public static function __callStatic($name, $arguments)
	{
		if (str_starts_with($name, 'is') && count($arguments) == 1) {
			$enumName = substr($name, 2);

			if (isset(self::getConstants()[$enumName])) {
				return static::from(self::getConstants()[$enumName]) == $arguments[0];
			}
		}

		if (isset(self::getConstants()[$name])) {
			$value = self::getConstants()[$name];
			return static::from($value);
		}

		throw new \BadMethodCallException(
			'Enum ' . static::class . '::' . $name . ' bestaat niet.'
		);
	}

	public function __call($name, $arguments)
	{
		if (str_starts_with($name, 'is')) {
			$enumName = substr($name, 2);

			if (isset(self::getConstants()[$enumName])) {
				return static::from(self::getConstants()[$enumName]) == $this;
			}
		}

		return static::__callStatic($name, $arguments);
	}

	/**
	 * @param $value
	 * @return static
	 */
	public static function from($value)
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

	public function getDescription()
	{
		return static::$mapChoiceToDescription[$this->value];
	}

	/**
	 * @return Enum[]
	 */
	public static function all()
	{
		return array_map(['static', 'from'], static::getEnumValues());
	}
}
