<?php

namespace CsrDelft\common\Util;

use CsrDelft\common\CsrException;
use Exception;
use PDOStatement;
use Traversable;

final class ArrayUtil
{
	/**
	 * @param Traversable|array
	 * @return array
	 */
	public static function as_array($value): array
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
	public static function array_shuffle(array $arr): array
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
	public static function in_array_i($needle, array $haystack): array
	{
		return in_array(strtolower($needle), array_map('strtolower', $haystack));
	}

	/**
	 * @source http://stackoverflow.com/a/3654335
	 * @param array $array
	 *
	 * @return array
	 */
	public static function array_filter_empty($array): array
	{
		return array_filter($array, [ArrayUtil::class, 'not_empty']);
	}

	public static function not_empty($value)
	{
		return $value != '';
	}

	/**
	 * Group by object property
	 *
	 * @param string $prop
	 * @param array|PDOStatement $in
	 * @param boolean $del delete from $in array
	 *
	 * @return array $out
	 */
	public static function group_by($prop, $in, $del = true): array
	{
		$del &= is_array($in);
		$out = [];
		foreach ($in as $i => $obj) {
			if (property_exists($obj, $prop)) {
				$key = $obj->$prop;
			} elseif (method_exists($obj, $prop)) {
				$key = $obj->$prop();
			} else {
				throw new Exception('Veld bestaat niet');
			}

			$out[$key][] = $obj; // add to array
			if ($del) {
				unset($in[$i]);
			}
		}
		return $out;
	}

	/**
	 * Group by distinct object property
	 *
	 * @param string $prop
	 * @param array|PDOStatement $in
	 * @param boolean $del delete from $in array
	 *
	 * @return array $out
	 */
	public static function group_by_distinct($prop, $in, $del = true): array
	{
		$del &= is_array($in);
		$out = [];
		foreach ($in as $i => $obj) {
			$out[$obj->$prop] = $obj; // overwrite existing
			if ($del) {
				unset($in[$i]);
			}
		}
		return $out;
	}
}
