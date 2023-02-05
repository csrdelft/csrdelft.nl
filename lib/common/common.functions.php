<?php

// C.S.R. Delft | pubcie@csrdelft.nl
// -------------------------------------------------------------------
// common.functions.php
// -------------------------------------------------------------------

if (!function_exists('str_starts_with')) {
	/**
	 * @param string $haystack
	 * @param string $needle
	 *
	 * @return boolean
	 */
	function str_starts_with($haystack, $needle)
	{
		return (string) $needle !== '' &&
			strncmp($haystack, $needle, strlen($needle)) === 0;
	}
}

if (!function_exists('str_ends_with')) {
	/**
	 * @param string $haystack
	 * @param string $needle
	 *
	 * @return boolean
	 */
	function str_ends_with($haystack, $needle)
	{
		return $needle === '' ||
			substr($haystack, -strlen($needle)) === (string) $needle;
	}
}

if (!function_exists('array_key_first')) {
	/**
	 * Deze functie bestaat wel in PHP 7.3
	 *
	 * Gets the first key of an array
	 *
	 * @param array $array
	 * @return mixed
	 */
	function array_key_first($array)
	{
		return $array ? array_keys($array)[0] : null;
	}
}

if (!function_exists('array_key_last')) {
	/**
	 * Deze functie bestaat wel in PHP 7.3
	 *
	 * Gets the last key of an array
	 *
	 * @param array $array
	 * @return mixed
	 */
	function array_key_last($array)
	{
		$key = null;

		if (is_array($array)) {
			end($array);
			$key = key($array);
		}

		return $key;
	}
}
