<?php


namespace CsrDelft\common;

use InvalidArgumentException;
use ReflectionClass;

abstract class Enum {
	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [];
	private static $constCacheArray = NULL;
	private static $instanceCacheArray = [];
	private $value;

	/**
	 * Gebruik de from methode om een enum te maken.
	 * Enum constructor.
	 * @param $value
	 */
	private function __construct($value) {
		if (!static::isValidValue($value)) {
			throw new InvalidArgumentException("Invalid enum value: " . $value . ' in ' . get_class(static::class));
		}
		$this->value = $value;
	}

	public static function isValidValue($value) {
		$values = array_values(self::getConstants());
		return in_array($value, $values, $strict = true);
	}

	private static function getConstants() {
		if (self::$constCacheArray == NULL) {
			self::$constCacheArray = [];
		}
		if (!array_key_exists(static::class, self::$constCacheArray)) {
			$reflect = new ReflectionClass(static::class);
			self::$constCacheArray[static::class] = $reflect->getConstants();
		}
		return self::$constCacheArray[static::class];
	}

	public static function getEnumValues() {
		return array_values(self::getConstants());
	}

	public static function getEnumDescriptions() {
		return static::$mapChoiceToDescription;
	}

	public static function isValidName($name, $strict = false) {
		$constants = self::getConstants();

		if ($strict) {
			return array_key_exists($name, $constants);
		}

		$keys = array_map('strtolower', array_keys($constants));
		return in_array(strtolower($name), $keys);
	}

	/**
	 * Returns a value when called statically like so: MyEnum::SOME_VALUE() given SOME_VALUE is a class constant
	 *
	 * @param string $name
	 * @param array $arguments
	 *
	 * @return static
	 * @psalm-pure
	 * @throws \BadMethodCallException
	 */
	public static function __callStatic($name, $arguments) {
		return static::from($name);
	}

	/**
	 * @param $value
	 * @return static
	 */
	public static function from($value) {
		if (!static::isValidValue($value)) {
			throw new InvalidArgumentException("Invalid enum value: " . $value . ' in ' . get_class(static::class));
		}

		if (!isset(self::$instanceCacheArray[static::class])) {
			self::$instanceCacheArray[static::class] = [];
		}

		if (!isset(self::$instanceCacheArray[static::class][$value])) {
			self::$instanceCacheArray[static::class][$value] = new static($value);
		}

		return self::$instanceCacheArray[static::class][$value];
	}

	public function getValue() {
		return $this->value;
	}

	public function getDescription() {
		return static::$mapChoiceToDescription[$this->value];
	}
}

