<?php

namespace CsrDelft\common\Util;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

final class ReflectionUtil
{
	/**
	 * Maak een ReflectionMethod voor een callable.
	 *
	 * @param callable $fn
	 * @return ReflectionMethod
	 * @throws ReflectionException
	 */
	public static function createReflectionMethod(callable $fn)
	{
		if (is_callable($fn)) {
			if (is_array($fn)) {
				if (is_object($fn[0])) {
					return new ReflectionMethod(\get_class($fn[0]), $fn[1]);
				} elseif (is_string($fn[0])) {
					return new ReflectionMethod($fn[0], $fn[1]);
				}
			} elseif (is_string($fn)) {
				if (strpos($fn, '::') !== false) {
					return new ReflectionMethod($fn);
				}
			} elseif (is_object($fn)) {
				return new ReflectionMethod(\get_class($fn), '__invoke');
			}
		}

		throw new InvalidArgumentException('Niet een callable');
	}

	/**
	 * Get the short name for a class
	 *
	 * @param object|string $class
	 *
	 * @return string
	 */
	public static function short_class($class)
	{
		return (new \ReflectionClass($class))->getShortName();
	}

	/**
	 * Haal de classname op uit een class beschrijving met namespace
	 *
	 * @param $className
	 *
	 * @return string
	 */
	public static function classNameZonderNamespace($className)
	{
		try {
			return (new ReflectionClass($className))->getShortName();
		} catch (ReflectionException $e) {
			return '';
		}
	}

	/**
	 * Haal de volledige classname met namespace op uit een beschrijving.
	 *
	 * @param $className
	 *
	 * @return string
	 */
	public static function className($className)
	{
		return preg_replace('/\\\\/', '-', $className);
	}
}
