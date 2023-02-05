<?php

// C.S.R. Delft | pubcie@csrdelft.nl
// -------------------------------------------------------------------
// common.functions.php
// -------------------------------------------------------------------
use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrException;
use CsrDelft\common\Util\FileUtil;
use CsrDelft\common\Util\UrlUtil;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\instellingen\InstellingenRepository;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\repository\instellingen\LidToestemmingRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\service\security\SuService;
use CsrDelft\view\Icon;

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
 * Group by object property
 *
 * @param string $prop
 * @param array|PDOStatement $in
 * @param boolean $del delete from $in array
 *
 * @return array $out
 */
function group_by($prop, $in, $del = true)
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
function group_by_distinct($prop, $in, $del = true)
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

/**
 * @return string
 * @deprecated Gebruik relatieve url of request_stack
 */
function getCsrRoot()
{
	$request = ContainerFacade::getContainer()
		->get('request_stack')
		->getCurrentRequest();

	return $request->getSchemeAndHttpHost();
}

/**
 * print_r een variabele met <pre>-tags eromheen.
 *
 * @param mixed $sString
 * @param string $cssID
 */
function debugprint($sString, $cssID = 'pubcie_debug')
{
	if (
		DEBUG ||
		LoginService::mag(P_ADMIN) ||
		ContainerFacade::getContainer()
			->get(SuService::class)
			->isSued()
	) {
		echo '<pre class="' . $cssID . '">' . print_r($sString, true) . '</pre>';
	}
}

/**
 * Shorthand for a curl request.
 * @param $url String The url for the request
 * @param array $options curl options
 * @return mixed The curl_exec result
 */
function curl_request($url, $options = [])
{
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt_array($curl, $options);
	$resp = curl_exec($curl);

	if ($resp == false) {
		throw new Exception(curl_error($curl));
	}

	return $resp;
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

/**
 * Versie van uniqid die het ook normaal op Windows doet. Als uniqid te snel achter elkaar aangeroepen wordt kan
 * twee keer hetzelfde gereturned worden. Op Windows gebeurt dit eerder.
 *
 * Replacet de punt omdat het anders geen javascript identifier kan zijn.
 *
 * Heeft de vorm:
 *  $prefix_f0f0f0f0f0f0f0_00000000
 *
 * @param string $prefix
 * @return string
 */
function uniqid_safe($prefix = '')
{
	return str_replace('.', '_', uniqid($prefix, true));
}

$configCache = [];

function sql_contains($field)
{
	return "%$field%";
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

/**
 * @param DateTimeInterface|null $date Datum om van te bepalen, bij null: vandaag
 * @param bool $substr Of alleen de laatste twee cijfers gegeven moeten worden
 * @return string Startjaar van boekjaar van gegeven datum
 */
function boekjaar(DateTimeInterface $date = null, bool $substr = false): string
{
	if ($date === null) {
		$date = date_create_immutable();
	}

	$jaar = intval($date->format('Y'));
	$wisseling = date_create_immutable('16-05-' . $jaar);
	if ($date < $wisseling) {
		$jaar--;
	}

	return $substr ? substr($jaar, 2, 2) : $jaar;
}
