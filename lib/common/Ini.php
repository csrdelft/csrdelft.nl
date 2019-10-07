<?php

namespace CsrDelft\common;

use Couchbase\Exception;

/**
 * Helper klasse om ini bestanden in de `etc` map uit te lezen.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 21/12/2018
 */
class Ini {
  const CRON = 'cron.ini';
  const CSRMAIL = 'csrmail.ini';
  const EMAILS = 'emails.ini';
  const GOOGLE = 'google.ini';
  const JWT = 'jwt.ini';
  const LDAP = 'ldap.ini';
  const MYSQL = 'mysql.ini';
  const PIN_TRANSACTIE_DOWNLOAD = 'pin_transactie_download.ini';
  const SLACK = 'slack.ini';
  const SOCCIE = 'soccie.ini';
  const SPONSOR_AFFILIATES_DOWNLOAD = 'sponsor_affiliates_download.ini';

	/**
	 * Runtime cache van de config
	 * @var array[]
	 */
  private static $configCache = [];

	/**
	 * Controleer of een bepaalde ini configuratie bestaat.
	 *
	 * @param string $iniFile
	 * @return bool
	 */
  public static function bestaat(string $iniFile) {
  	if (isset(static::$configCache[$iniFile])) {
  		return true;
		}

		return file_exists(ETC_PATH . $iniFile);
	}

	/**
	 * Lees een configuratie. Gaat er vanuit dat de configuratie bestaat.
	 *
	 * @param string $iniFile
	 * @param string|null $key De sleutel in de configuratie, null voor de volledige configuratie.
	 * @return array|mixed|string
	 */
  public static function lees(string $iniFile, string $key = null) {
		if (!isset(static::$configCache[$iniFile])) {
			$config = parse_ini_file(ETC_PATH . $iniFile);
			static::$configCache[$iniFile] = $config ?? [];
		}

		$config = static::$configCache[$iniFile];

		if ($config == false) {
			throw new CsrException('Configuratie bestand "' . $iniFile . '" bestaat niet of is leeg');
		}

		if ($key === null) {
			return $config ?? [];
		} elseif (isset($config[$key])) {
			return $config[$key];
		} else {
			throw new CsrException('Instelling "' . $key . '" in "' . $iniFile . '" niet gevonden.');
		}
	}

	/**
	 * Lees een configuratie, maar geef geen foutmelding als de configuratie niet bestaat.
	 * @param string $iniFile
	 * @param string $key
	 * @param string $default
	 * @return array|mixed|string
	 */
	public static function leesOfStandaard(string $iniFile, string $key, string $default = '') {
  	try {
  		return static::lees($iniFile, $key);
		} catch (CsrException $e) {
  		return $default;
		}
	}
}
