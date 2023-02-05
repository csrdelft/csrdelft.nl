<?php

namespace CsrDelft\common\Util;

use CsrDelft\common\CsrException;
use Traversable;

final class ArrayUtil
{
	/**
	 * @param Traversable|array
	 * @return array
	 */
	public static function as_array($value)
	{
		if (is_array($value)) {
			return $value;
		} elseif ($value instanceof Traversable) {
			return iterator_to_array($value);
		}
		throw new CsrException('Geen array of iterable');
	}

	/**
	 * Versie van shuffle die niet de originele array veranderd en wel een waarde terug geeft.
	 *
	 * @param array $arr
	 * @return array
	 */
	public static function array_shuffle(array $arr)
	{
		shuffle($arr);

		return $arr;
	}

	/**
	 * Case insensitive in_array
	 *
	 * @source http://stackoverflow.com/a/2166524
	 * @param string $needle
	 * @param array $haystack
	 *
	 * @return boolean
	 */
	public static function in_array_i($needle, array $haystack)
	{
		return in_array(strtolower($needle), array_map('strtolower', $haystack));
	}

	/**
	 * @source http://stackoverflow.com/a/3654335
	 * @param array $array
	 *
	 * @return array
	 */
	public static function array_filter_empty($array)
	{
		return array_filter($array, [ArrayUtil::class, 'not_empty']);
	}

	public static function not_empty($value)
	{
		return $value != '';
	}
}
