<?php

// C.S.R. Delft | pubcie@csrdelft.nl
// -------------------------------------------------------------------
// common.functions.php
// -------------------------------------------------------------------
use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\instellingen\InstellingenRepository;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\repository\instellingen\LidToestemmingRepository;

define('LONG_DATE_FORMAT', 'EE d MMM'); // Ma 3 Jan
define('DATE_FORMAT', 'y-MM-dd');
define('DATETIME_FORMAT', 'y-MM-dd HH:mm:ss');
define('TIME_FORMAT', 'HH:mm');

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

/**
 * @param Profiel $profiel
 * @param string|string[] $key
 * @param string $cat
 * @param string $uitzondering Sommige commissie mogen wel dit veld zien.
 * @return bool
 */
function is_zichtbaar(
	$profiel,
	$key,
	$cat = 'profiel',
	$uitzondering = P_LEDEN_MOD
) {
	$lidToestemmingRepository = ContainerFacade::getContainer()->get(
		LidToestemmingRepository::class
	);
	if (is_array($key)) {
		foreach ($key as $item) {
			if (
				!$lidToestemmingRepository->toestemming(
					$profiel,
					$item,
					$cat,
					$uitzondering
				)
			) {
				return false;
			}
		}

		return true;
	}

	return $lidToestemmingRepository->toestemming(
		$profiel,
		$key,
		$cat,
		$uitzondering
	);
}

function lid_instelling($module, $key)
{
	return ContainerFacade::getContainer()
		->get(LidInstellingenRepository::class)
		->getValue($module, $key);
}

function instelling($module, $key)
{
	return ContainerFacade::getContainer()
		->get(InstellingenRepository::class)
		->getValue($module, $key);
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
