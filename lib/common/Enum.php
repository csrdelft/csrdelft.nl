<?php


namespace CsrDelft\common;

use ReflectionClass;

abstract class Enum {
	private static $constCacheArray = NULL;
	private static $instanceCacheArray = [];

	private $value;

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [];

	/**
	 * Gebruik de from methode om een enum te maken.
	 * Enum constructor.
	 * @param $value
	 */
	private function __construct($value) {
		if (!static::isValidValue($value)) {
			throw new \InvalidArgumentException("Invalid enum value: " . $value . ' in ' . get_class(static::class));
		}
		$this->value = $value;
	}

	/**
	 * @param $value
	 * @return static
	 */
	public static function from($value) {
		if (!static::isValidValue($value)) {
			throw new \InvalidArgumentException("Invalid enum value: " . $value . ' in ' . get_class(static::class));
		}

		if (!isset(static::$instanceCacheArray[$value])) {
			static::$instanceCacheArray[$value] = new static($value);
		}

		return static::$instanceCacheArray[$value];
	}

	public static function getEnumValues() {
		return array_values(self::getConstants());
	}

	public static function getEnumDescriptions() {
		return static::$mapChoiceToDescription;
	}

	public static function isValidValue($value) {
		$values = array_values(self::getConstants());
		return in_array($value, $values, $strict = true);
	}

	private static function getConstants() {
		if (self::$constCacheArray == NULL) {
			self::$constCacheArray = [];
		}
		$calledClass = get_called_class();
		if (!array_key_exists($calledClass, self::$constCacheArray)) {
			$reflect = new ReflectionClass($calledClass);
			self::$constCacheArray[$calledClass] = $reflect->getConstants();
		}
		return self::$constCacheArray[$calledClass];
	}

	public static function isValidName($name, $strict = false) {
		$constants = self::getConstants();

		if ($strict) {
			return array_key_exists($name, $constants);
		}

		$keys = array_map('strtolower', array_keys($constants));
		return in_array(strtolower($name), $keys);
	}

	public function getValue() {
		return $this->value;
	}

	public function getDescription() {
		return static::$mapChoiceToDescription[$this->value];
	}
}

