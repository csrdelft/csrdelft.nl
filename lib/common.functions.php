<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# common.functions.php
# -------------------------------------------------------------------
# Ingeladen door composer, zie composer.json
use CsrDelft\Icon;
use CsrDelft\lid\LidZoeker;
use CsrDelft\MijnSqli;
use CsrDelft\model\entity\Profiel;
use CsrDelft\model\InstellingenModel;
use CsrDelft\model\LidToestemmingModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\Persistence\DatabaseAdmin;

/**
 * @source http://stackoverflow.com/questions/834303/php-startswith-and-endswith-functions
 * @param string $haystack
 * @param string $needle
 *
 * @return boolean
 */
function startsWith($haystack, $needle) {
	return $needle === "" || strpos($haystack, $needle) === 0;
}

/**
 * @source http://stackoverflow.com/questions/834303/php-startswith-and-endswith-functions
 * @param string $haystack
 * @param string $needle
 *
 * @return boolean
 */
function endsWith($haystack, $needle) {
	return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}

/**
 * @source http://stackoverflow.com/a/3654335
 * @param array $array
 *
 * @return array
 */
function array_filter_empty($array) {
	return array_filter($array, 'not_empty');
}

function not_empty($value) {
	return $value != '';
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
function in_array_i($needle, array $haystack) {
	return in_array(strtolower($needle), array_map('strtolower', $haystack));
}

/**
 * Group by object property
 *
 * @param string $prop
 * @param array $in
 * @param boolean $del delete from $in array
 *
 * @return array $out
 */
function group_by($prop, $in, $del = true) {
	$del &= is_array($in);
	$out = array();
	foreach ($in as $i => $obj) {
		$out[$obj->$prop][] = $obj; // add to array
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
 * @param array $in
 * @param boolean $del delete from $in array
 *
 * @return array $out
 */
function group_by_distinct($prop, $in, $del = true) {
	$del &= is_array($in);
	$out = array();
	foreach ($in as $i => $obj) {
		$out[$obj->$prop] = $obj; // overwrite existing
		if ($del) {
			unset($in[$i]);
		}
	}
	return $out;
}

/**
 * Set cookie with url to go back to after login.
 *
 * @param string $url
 */
function setGoBackCookie($url) {
	if ($url == null) {
		unset($_COOKIE['goback']);
		setcookie('goback', null, -1, '/', CSR_DOMAIN, FORCE_HTTPS, true);
	} else {
		setcookie('goback', $url, time() + (int)InstellingenModel::get('beveiliging', 'session_lifetime_seconds'), '/', CSR_DOMAIN, FORCE_HTTPS, true);
	}
}

/**
 * Set cookie with token to automatically login.
 *
 * @param string $token
 */
function setRememberCookie($token) {
	if ($token == null) {
		unset($_COOKIE['remember']);
		setcookie('remember', null, -1, '/', CSR_DOMAIN, FORCE_HTTPS, true);
	} else {
		setcookie('remember', $token, time() + (int)InstellingenModel::get('beveiliging', 'remember_login_seconds'), '/', CSR_DOMAIN, FORCE_HTTPS, true);
	}
}

/**
 * @return int
 */
function getSessionMaxLifeTime() {
	$lifetime = (int)InstellingenModel::get('beveiliging', 'session_lifetime_seconds');
	// Sync lifetime of FS based PHP session with DB based C.S.R. session
	$gc = (int)ini_get('session.gc_maxlifetime');
	if ($gc > 0 AND $gc < $lifetime) {
		$lifetime = $gc;
	}
	return $lifetime;
}

if (!function_exists('redirect')) {
    /**
     * Invokes a client page (re)load the url.
     *
     * @param string $url
     * @param boolean $refresh allow a refresh; redirect to CSR_ROOT otherwise
     */
    function redirect($url = null, $refresh = true)
    {
        if (empty($url)) {
            $url = REQUEST_URI;
        }
        if (!$refresh AND $url == REQUEST_URI) {
            $url = CSR_ROOT;
        }
        if (!startsWith($url, CSR_ROOT)) {
            $url = CSR_ROOT . $url;
        }
        header('location: ' . $url);
        exit;
    }
}

/**
 * rawurlencode() met uitzondering van slashes.
 *
 * @param string $url
 *
 * @return string
 */
function direncode($url) {
	return str_replace('%2F', '/', rawurlencode($url));
}

/**
 * @param $string string
 *
 * @return bool
 */
function is_utf8($string) {
	return checkEncoding($string, 'UTF-8');
}

function checkEncoding($string, $string_encoding) {
	$fs = $string_encoding == 'UTF-8' ? 'UTF-32' : $string_encoding;
	$ts = $string_encoding == 'UTF-32' ? 'UTF-8' : $string_encoding;
	return $string === mb_convert_encoding(mb_convert_encoding($string, $fs, $ts), $ts, $fs);
}

/**
 * @source http://stackoverflow.com/a/13733588
 */
function crypto_rand_token($length) {
	$token = '';
	$codeAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$codeAlphabet .= 'abcdefghijklmnopqrstuvwxyz';
	$codeAlphabet .= '0123456789';
	for ($i = 0; $i < $length; $i++) {
		$token .= $codeAlphabet[crypto_rand_secure(0, strlen($codeAlphabet))];
	}
	return $token;
}

/**
 * @param $min int
 * @param $max int
 *
 * @return mixed
 */
function crypto_rand_secure($min, $max) {
	$range = $max - $min;
	if ($range < 0) {
		return $min; // not so random...
	}
	$log = log($range, 2);
	$bytes = (int)($log / 8) + 1; // length in bytes
	$bits = (int)$log + 1; // length in bits
	$filter = (int)(1 << $bits) - 1; // set all lower bits to 1
	do {
		$rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
		$rnd = $rnd & $filter; // discard irrelevant bits
	} while ($rnd >= $range);
	return $min + $rnd;
}

/**
 * @param $date
 * @param string $format
 *
 * @return bool
 */
function valid_date($date, $format = 'Y-m-d H:i:s') {
	$d = DateTime::createFromFormat($format, $date);
	return $d && $d->format($format) == $date;
}

/**
 * @param $name string
 *
 * @return bool
 */
function valid_filename($name) {
	return preg_match('/^(?:[a-z0-9 \-_\(\)é]|\.(?!\.))+$/iD', $name);
}

/**
 * @source http://www.regular-expressions.info/email.html
 * @param $email
 *
 * @return bool
 */
function email_like($email) {
	if (empty($email)) {
		return false;
	}
	return preg_match("/^[a-zA-Z0-9!#$%&'\*\+=\?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&'\*\+=\?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+(?:[a-zA-Z]{2,})\b$/", $email);
}

/**
 * @source https://mathiasbynens.be/demo/url-regex
 * @param $url
 *
 * @return bool
 */
function url_like($url) {
	if (empty($url)) {
		return false;
	}
	return preg_match('_^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:/[^\s]*)?$_iuS', $url);
}

function external_url($url, $label) {
	$url = filter_var($url, FILTER_SANITIZE_URL);
	if ($url AND (url_like($url) OR url_like(CSR_ROOT . $url))) {
		if (startsWith($url, 'http://') OR startsWith($url, 'https://')) {
			$extern = ' target="_blank"';
		} else {
			$extern = '';
		}
		$result = '<a href="' . $url . '" title="' . $url . '"' . $extern . '>' . $label . '</a>';
	} else {
		$result = $url;
	}
	return $result;
}

/**
 * Is de huidige host genaamd 'syrinx'?
 * @return boolean
 */
function isSyrinx() {
	return 'syrinx' === php_uname('n');
}

/**
 * @param int $timestamp optional
 *
 * @return string current DateTime formatted Y-m-d H:i:s
 */
function getDateTime($timestamp = null) {
	if ($timestamp === null) {
		$timestamp = time();
	}
	return date('Y-m-d H:i:s', $timestamp);
}

/**
 * @param int $timestamp
 *
 * @return string aangepast ISO-8601 weeknummer met zondag als eerste dag van de week
 */
function getWeekNumber($timestamp) {
	if (date('w', $timestamp) == 0) {
		return date('W', strtotime('+1 day', $timestamp));
	} else {
		return date('W', $timestamp);
	}
}

/**
 * @param string $datum moet beginnen met 'yyyy-mm-dd' (wat daarna komt maakt niet uit)
 *
 * @return boolean true als $datum geldig is volgens checkdate(); false otherwise
 */
function isGeldigeDatum($datum) {
	// De string opdelen en checken of er genoeg delen zijn.
	$delen = explode('-', $datum);
	if (count($delen) < 3) {
		return false;
	}
	// Checken of we geldige strings hebben, voordat we ze casten naar ints.
	$jaar = $delen[0];
	if (!is_numeric($jaar) OR strlen($jaar) != 4) {
		return false;
	}
	$maand = $delen[1];
	if (!is_numeric($maand) OR strlen($maand) != 2) {
		return false;
	}
	$dag = substr($delen[2], 0, 2); // Alleen de eerste twee karakters pakken.
	if (!is_numeric($dag) OR strlen($dag) != 2) {
		return false;
	}
	// De strings casten naar ints en de datum laten checken.
	return checkdate((int)$maand, (int)$dag, (int)$jaar);
}

/**
 * print_r een variabele met <pre>-tags eromheen.
 *
 * @param mixed $sString
 * @param string $cssID
 */
function debugprint($sString, $cssID = 'pubcie_debug') {
	if (DEBUG OR LoginModel::mag('P_ADMIN') OR LoginModel::instance()->isSued()) {
		echo '<pre class="' . $cssID . '">' . print_r($sString, true) . '</pre>';
	}
}

/**
 * Probeert uit invoer van uids of namen per zoekterm een unieke uid te bepalen, zoniet een lijstje suggesties en anders false.
 *
 * @param  string $sNamen string met namen en/of uids op nieuwe regels en/of gescheiden door komma's
 * @param   array|string $filter zoekfilter voor LidZoeker::zoekLeden, toegestane input: '', 'leden', 'oudleden' of array met stati
 *
 * @return  bool false bij geen matches
 *      of een array met per zoekterm een entry met een unieke uid en naam òf een array met naamopties.
 * Voorbeeld:
 * Input: $sNamen = 'Lid, Klaassen'
 * Output: Array(
 * [0] => Array (
 * [naamOpties] => Array (
 * [0] => Array (
 * [uid] => 4444
 * [naam] => Oud Lid
 * )
 * [1] => Array (
 * [uid] => x101
 * [naam] => Jan Lid
 * )
 * [2] => Array (
 * ...
 * )
 * )
 * [1] => Array (
 * [uid] => 0431
 * [naam] => Jan Klaassen
 * )
 * )
 */
function namen2uid($sNamen, $filter = 'leden') {
	$return = array();
	$sNamen = trim($sNamen);
	$sNamen = str_replace(array(', ', "\r\n", "\n"), ',', $sNamen);
	$aNamen = explode(',', $sNamen);
	foreach ($aNamen as $sNaam) {
		$aNaamOpties = array();
		require_once 'lid/LidZoeker.php';
		$aZoekNamen = LidZoeker::zoekLeden($sNaam, 'naam', 'alle', 'achternaam', $filter, array('uid', 'voornaam', 'tussenvoegsel', 'achternaam'));
		if (count($aZoekNamen) == 1) {
			$naam = $aZoekNamen[0]['voornaam'] . ' ';
			if (trim($aZoekNamen[0]['tussenvoegsel']) != '') {
				$naam .= $aZoekNamen[0]['tussenvoegsel'] . ' ';
			}
			$naam .= $aZoekNamen[0]['achternaam'];
			$return[] = array('uid' => $aZoekNamen[0]['uid'], 'naam' => $naam);
		} elseif (count($aZoekNamen) == 0) {

		} else {
			//geen enkelvoudige match, dan een array teruggeven
			foreach ($aZoekNamen as $aZoekNaam) {
				$profiel = ProfielModel::get($aZoekNaam['uid']);
				$aNaamOpties[] = array(
					'uid' => $aZoekNaam['uid'],
					'naam' => $profiel->getNaam());
			}
			$return[]['naamOpties'] = $aNaamOpties;
		}
	}
	if (count($return) === 0)
		return false;
	return $return;
}

function reldate($datum) {
	$moment = strtotime($datum);
	/* $nu = time();
	  $verschil = $nu - $moment;
	  if ($verschil <= 60) {
	  $return = $verschil . ' ';
	  if ($verschil == 1) {
	  $return .= 'seconde';
	  } else {
	  $return .= 'seconden';
	  }
	  $return .= ' geleden';
	  } elseif ($verschil <= 60 * 60) {
	  $return = floor($verschil / 60);
	  if (floor($verschil / 60) == 1) {
	  $return .= ' minuut';
	  } else {
	  $return .= ' minuten';
	  }
	  $return .= ' geleden';
	  } elseif ($verschil <= (60 * 60 * 4)) {
	  $return = floor($verschil / (60 * 60)) . ' uur geleden';
	  } else */
	if (date('Y-m-d') == date('Y-m-d', $moment)) {
		$return = 'vandaag om ' . strftime('%H:%M', $moment);
	} elseif (date('Y-m-d', $moment) == date('Y-m-d', strtotime('1 day ago'))) {
		$return = 'gisteren om ' . strftime('%H:%M', $moment);
	} else {
		$return = strftime('%A %e %B %Y om %H:%M', $moment); // php-bug: %e does not work on Windows
	}
	return '<abbr class="timeago" title="' . date('Y-m-d\TG:i:sO', $moment) . '">' . $return . '</abbr>'; // ISO8601
}

/**
 * Voeg landcode toe als nummer met 0 begint of vervang 00 met +
 *
 * @param string $phonenumber
 * @param string $prefix
 *
 * @return string
 */
function internationalizePhonenumber($phonenumber, $prefix = '+31') {
	$number = str_replace(array(' ', '-'), '', $phonenumber);
	if ($number[0] === '0') {
		// vergelijken met == 0 levert problemen op want (int) '+' = 0 dankzij php
		if ($number[1] === '0') {
			return '+' . substr($number, 2);
		}
		return $prefix . substr($number, 1);
	} else {
		return $phonenumber;
	}
}

/**
 * Plaatje vierkant croppen.
 * @source http://abeautifulsite.net/blog/2009/08/cropping-an-image-to-make-square-thumbnails-in-php/
 */
function square_crop($src_image, $dest_image, $thumb_size = 64, $jpg_quality = 90) {

	// Get dimensions of existing image
	$image = getimagesize($src_image);

	// Check for valid dimensions
	if ($image[0] <= 0 || $image[1] <= 0)
		return false;

	// Determine format from MIME-Type
	$image['format'] = strtolower(preg_replace('/^.*?\//', '', $image['mime']));

	// Import image
	switch ($image['format']) {
		case 'jpg':
		case 'jpeg':
			$image_data = imagecreatefromjpeg($src_image);
			break;
		case 'png':
			$image_data = imagecreatefrompng($src_image);
			break;
		case 'gif':
			$image_data = imagecreatefromgif($src_image);
			break;
		default:
			// Unsupported format
			return false;
	}

	// Verify import
	if ($image_data == false) {
		return false;
	}

	// Calculate measurements
	if ($image[0] > $image[1]) {
		// For landscape images
		$x_offset = ($image[0] - $image[1]) / 2;
		$y_offset = 0;
		$square_size = $image[0] - ($x_offset * 2);
	} else {
		// For portrait and square images
		$x_offset = 0;
		$y_offset = ($image[1] - $image[0]) / 2;
		$square_size = $image[1] - ($y_offset * 2);
	}

	// Resize and crop
	$canvas = imagecreatetruecolor($thumb_size, $thumb_size);
	if (imagecopyresampled($canvas, $image_data, 0, 0, $x_offset, $y_offset, $thumb_size, $thumb_size, $square_size, $square_size)) {

		// Create thumbnail
		switch (strtolower(preg_replace('/^.*\./', '', $dest_image))) {
			case 'jpg':
			case 'jpeg':
				$return = imagejpeg($canvas, $dest_image, $jpg_quality);
				break;
			case 'png':
				$return = imagepng($canvas, $dest_image);
				break;
			case 'gif':
				$return = imagegif($canvas, $dest_image);
				break;
			default:
				// Unsupported format
				$return = false;
				break;
		}

		//plaatje ook voor de webserver leesbaar maken.
		if ($return) {
			chmod($dest_image, 0644);
		}
		return $return;
	} else {
		return false;
	}
}

function format_filesize($size) {
	$units = array(' B', ' KB', ' MB', ' GB', ' TB');
	for ($i = 0; $size >= 1024 && $i < 4; $i++) {
		$size /= 1024;
	}
	return round($size, 2) . $units[$i];
}

/**
 * This function transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in this case)
 *
 * @source http://stackoverflow.com/a/22500394
 */
function convertPHPSizeToBytes($sSize) {
	if (is_numeric($sSize)) {
		return $sSize;
	}
	$sSuffix = substr($sSize, -1);
	$iValue = substr($sSize, 0, -1);
	switch (strtoupper($sSuffix)) {
		/** @noinspection PhpMissingBreakStatementInspection */
		case 'P':
			$iValue *= 1024;
		/** @noinspection PhpMissingBreakStatementInspection */
		case 'T':
			$iValue *= 1024;
		/** @noinspection PhpMissingBreakStatementInspection */
		case 'G':
			$iValue *= 1024;
		/** @noinspection PhpMissingBreakStatementInspection */
		case 'M':
			$iValue *= 1024;
		/** @noinspection PhpMissingBreakStatementInspection */
		case 'K':
			$iValue *= 1024;
		default:
			break;
	}
	return $iValue;
}

function getMaximumFileUploadSize() {
	return min(convertPHPSizeToBytes(ini_get('post_max_size')), convertPHPSizeToBytes(ini_get('upload_max_filesize')));
}

function printDebug() {
	if (DEBUG OR (LoginModel::mag('P_ADMIN') OR LoginModel::instance()->isSued())) {
		echo '<a id="mysql_debug_toggle" onclick="$(this).replaceWith($(\'#mysql_debug\').toggle());">DEBUG</a>';
		echo '<div id="mysql_debug" class="pre">' . getDebug() . '</div>';
	}
}

function getDebug(
	$get = true, $post = true, $files = true, $cookie = true, $session = true, $server = true, $sql = true,
	$sqltrace = true
) {
	$debug = '';
	if ($get) {
		$debug .= '<hr />GET<hr />' . htmlspecialchars(print_r($_GET, true));
	}
	if ($post) {
		$debug .= '<hr />POST<hr />' . htmlspecialchars(print_r($_POST, true));
	}
	if ($files) {
		$debug .= '<hr />FILES<hr />' . htmlspecialchars(print_r($_FILES, true));
	}
	if ($cookie) {
		$debug .= '<hr />COOKIE<hr />' . htmlspecialchars(print_r($_COOKIE, true));
	}
	if ($session) {
		//$debug .= '<hr />SESSION<hr />' . htmlspecialchars(print_r($_SESSION, true));
	}
	if ($server) {
		$debug .= '<hr />SERVER<hr />' . htmlspecialchars(print_r($_SERVER, true));
	}
	if ($sql) {
		$debug .= '<hr />SQL<hr />' . htmlspecialchars(print_r(array("Admin" => DatabaseAdmin::instance()->getQueries(), "PDO" => Database::instance()->getQueries(), "MySql" => MijnSqli::instance()->getQueries()), true));
	}
	if ($sqltrace) {
		$debug .= '<hr />SQL-backtrace<hr />' . htmlspecialchars(print_r(Database::instance()->getTrace(), true));
	}
	return $debug;
}

/**
 * Stores a message.
 *
 * Levels can be:
 *
 * -1 error / danger
 *  0 info
 *  1 success
 *  2 warning / notify
 *
 * @see    getMelding()
 * gebaseerd op DokuWiki code
 */
function setMelding($msg, $lvl) {
	$errors[-1] = 'danger';
	$errors[0] = 'info';
	$errors[1] = 'success';
	$errors[2] = 'warning';
	$msg = trim($msg);
	if (!empty($msg) AND ($lvl === -1 OR $lvl === 0 OR $lvl === 1 OR $lvl === 2)) {
		if (!isset($_SESSION['melding'])) {
			$_SESSION['melding'] = array();
		}
		// gooit verouderde gegevens weg
		if (is_string($_SESSION['melding'])) {
			$_SESSION['melding'] = array();
		}
		$_SESSION['melding'][] = array('lvl' => $errors[$lvl], 'msg' => $msg);
	}
}

/**
 * Geeft berichten weer die opgeslagen zijn in de sessie met met setMelding($msg, $lvl)
 *
 * @return string html van melding(en) of lege string
 */
function getMelding() {
    if ($melding = session()->get('message')) {
        $sMelding = '<div id="melding">';
        $sMelding .= '<div class="alert alert-1">';
        $sMelding .= Icon::getTag('alert-1');
        $sMelding .= $melding;
        $sMelding .= '</div>';
        $sMelding .= '</div>';
        return $sMelding;
    }

	if (isset($_SESSION['melding']) AND is_array($_SESSION['melding'])) {
		$sMelding = '<div id="melding">';
		$shown = array();
		foreach ($_SESSION['melding'] as $msg) {
			$hash = md5($msg['msg']);
			//if (isset($shown[$hash]))
			//	continue; // skip double messages
			$sMelding .= '<div class="alert alert-' . $msg['lvl'] . '">';
			$sMelding .= Icon::getTag('alert-' . $msg['lvl']);
			$sMelding .= $msg['msg'];
			$sMelding .= '</div>';
			$shown[$hash] = 1;
		}
		$sMelding .= '</div>';
		// maar één keer tonen, de melding.
		unset($_SESSION['melding']);
		return $sMelding;
	} else {
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
function className($className) {
	return preg_replace('/\\\\/', '-', $className);
}

/**
 * Haal de classname op uit een class beschrijving met namespace
 *
 * @param $className
 *
 * @return string
 */
function classNameZonderNamespace($className) {
    try {
        return (new \ReflectionClass($className))->getShortName();
    } catch (ReflectionException $e) {
        return '';
    }
}

/**
 * Haal string naam op voor error uit php
 *
 * @param int $type
 *
 * @return string
 */
function errorName($type) {
	$errors = [
		E_ERROR => 'E_ERROR',
		E_WARNING => 'E_WARNING',
		E_PARSE => 'E_PARSE',
		E_NOTICE => 'E_NOTICE',
		E_CORE_ERROR => 'E_CORE_ERROR',
		E_CORE_WARNING => 'E_CORE_WARNING',
		E_COMPILE_ERROR => 'E_COMPILE_ERROR',
		E_COMPILE_WARNING => 'E_COMPILE_WARNING',
		E_USER_ERROR => 'E_USER_ERROR',
		E_USER_WARNING => 'E_USER_WARNING',
		E_USER_NOTICE => 'E_USER_NOTICE',
		E_STRICT => 'E_STRICT',
		E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
		E_DEPRECATED => 'E_DEPRECATED',
		E_USER_DEPRECATED => 'E_USER_DEPRECATED',
	];
	if (key_exists($type, $errors)) {
		return $errors[$type];
	} else {
		return 'Onbekende fout ' . strval($type);
	}
}

/**
 * @param string $voornaam
 * @param string $tussenvoegsel
 * @param string $achternaam
 *
 * @return string
 */
function aaidrom($voornaam, $tussenvoegsel, $achternaam) {
	$voornaam = mb_strtolower($voornaam);
	$achternaam = mb_strtolower($achternaam);

	$voor = [];
	preg_match('/^([^aeiuoyáéíóúàèëïöü]*)(.*)$/u', $voornaam, $voor);
	$achter = [];
	preg_match('/^([^aeiuoyáéíóúàèëïöü]*)(.*)$/u', $achternaam, $achter);

	$nwvoor = preg_replace("/^Ij/", "IJ", ucwords($achter[1] . $voor[2]), 1);
	$nwachter = preg_replace("/^Ij/", "IJ", ucwords($voor[1] . $achter[2]), 1);

	return sprintf("%s %s%s", $nwvoor, !empty($tussenvoegsel) ? $tussenvoegsel . ' ' : '', $nwachter);
}

function url2absolute($baseurl, $relativeurl) {

	// if the relative URL is scheme relative then treat it differently
	if (substr($relativeurl, 0, 2) === "//") {
		if (parse_url($baseurl, PHP_URL_SCHEME) != null) {
			return parse_url($baseurl, PHP_URL_SCHEME) . ":" . $relativeurl;
		} else { // assume HTTP
			return "http:" . $relativeurl;
		}
	}

	// if the relative URL points to the root then treat it more simply
	if (substr($relativeurl, 0, 1) === "/") {
		$parts = parse_url($baseurl);
		$return = $parts['scheme'] . ":";
		$return .= ($parts['scheme'] === "file") ? "///" : "//";
		// username:password@host:port ... could go here too!
		$return .= $parts['host'] . $relativeurl;
		return $return;
	}

	// If the relative URL is actually an absolute URL then just use that
	if (parse_url($relativeurl, PHP_URL_SCHEME) !== null) {
		return $relativeurl;
	}

	$parts = parse_url($baseurl);

	// Chop off the query string in a base URL if it is there
	if (isset($parts['query'])) {
		$baseurl = strstr($baseurl, '?', true);
	}

	// The rest is adapted from Puggan Se

	$minpartsinfinal = 3; // for everything except file:///
	if ($parts['scheme'] === "file") {
		$minpartsinfinal = 4;
	}

	// logic for username:password@host:port ... query string etc. could go here too ... somewhere?

	$basepath = explode('/', $baseurl); // will this handle correctly when query strings have '/'
	$relpath = explode('/', $relativeurl);

	array_pop($basepath);

	$returnpath = array_merge($basepath, $relpath);
	$returnpath = array_reverse($returnpath);

	$parents = 0;
	foreach ($returnpath as $part_nr => $part_value) {
		/* if we find '..', remove this and the next element */
		if ($part_value == '..') {
			$parents++;
			unset($returnpath[$part_nr]);
		} /* if we find '.' remove this element */
		else if ($part_value == '.') {
			unset($returnpath[$part_nr]);
		} /* if this is a normal element, and we have unhandled '..', then remove this */
		else if ($parents > 0) {
			unset($returnpath[$part_nr]);
			$parents--;
		}
	}
	$returnpath = array_reverse($returnpath);
	if (count($returnpath) < $minpartsinfinal) {
		return FALSE;
	}
	return implode('/', $returnpath);
}

/**
 * Shorthand for a curl request.
 * @param $url String The url for the request
 * @param array $options curl options
 * @return mixed The curl_exec result
 */
function curl_request($url, $options = []) {
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt_array($curl, $options);
	return curl_exec($curl);
}

/**
 * Follow an url to its final location taking http redirects and meta refreshes into account.
 *
 * @param String $url The url to follow to its final location
 * @param array $options A curl_setopt_array compatible array
 * @return String The final url location
 */
function curl_follow_location($url, $options = []) {
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt_array($curl, $options);
	$xpath = init_xpath(curl_exec($curl));
	$location = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);

	$refresh = $xpath->query('//meta[translate(@http-equiv, "abcdefghijklmnopqrstuvwxyz", "ABCDEFGHIJKLMNOPQRSTUVWXYZ")="REFRESH"]');
	if ($refresh->length > 0) {
		preg_match('/(?<=url=)(.*)/i', $refresh->item(0)->getAttribute('content'), $matches);
		$refreshUrl = trim($matches[0]);

        if (empty($refreshUrl)) {
            return $location;
        }

		if (!startsWith($refreshUrl, 'http')) {
			$refreshUrl = http_build_url($location, $refreshUrl, HTTP_URL_REPLACE | HTTP_URL_JOIN_PATH);
		}

		return curl_follow_location($refreshUrl, $options);
	}

	return $location;
}

/**
 * Create an xpath object from an HTML string.
 *
 * @param $html String the HTML string to create the xpath object from
 * @return \DOMXPath The xpath object
 */
function init_xpath($html) {
	$xml = new \DOMDocument();
	$xml->loadHTML($html);
	return new \DOMXPath($xml);
}

/**
 * Controleer of een mime-type bij een bestandsnaam past, onbekende bestandsnamen worden afgewezen.
 *
 * @param string $filename
 * @param string $mime
 * @return bool
 */
function checkMimetype($filename, $mime) {
	$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

	$mimeToExtension = [
		'application/x-7z-compressed' => '7z',
		'audio/x-aac' => 'aac',
		'application/postscript' => ['ai', 'eps', 'ps'],
		'audio/x-aiff' => 'aif',
		'text/plain' => ['asc', 'ini', 'log', 'txt'],
		'video/x-ms-asf' => 'asf',
		'application/atom+xml' => 'atom',
		'video/x-msvideo' => 'avi',
		'image/bmp' => 'bmp',
		'application/x-bzip2' => 'bz2',
		'application/pkix-cert' => 'cer',
		'application/pkix-crl' => 'crl',
		'application/x-x509-ca-cert' => 'crt',
		'text/css' => 'css',
		'text/csv' => 'csv',
		'application/cu-seeme' => 'cu',
		'application/x-debian-package' => 'deb',
		'application/msword' => 'doc',
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
		'application/x-dvi' => 'dvi',
		'application/vnd.ms-fontobject' => 'eot',
		'application/epub+zip' => 'epub',
		'text/x-setext' => 'etx',
		'audio/flac' => 'flac',
		'video/x-flv' => 'flv',
		'image/gif' => 'gif',
		'application/gzip' => 'gz',
		'text/html' => ['htm', 'html'],
		'image/x-icon' => 'ico',
		'text/calendar' => 'ics',
		'application/x-iso9660-image' => 'iso',
		'application/java-archive' => 'jar',
		'image/jpeg' => ['jpe', 'jpeg', 'jpg'],
		'text/javascript' => 'js',
		'application/json' => 'json',
		'application/x-latex' => 'latex',
		'audio/mp4' => 'm4a',
		'video/mp4' => ['m4v', 'mp4', 'mp4a', 'mp4v', 'mpg4'],
		'audio/midi' => ['mid', 'midi'],
		'video/quicktime' => ['mov', 'qt'],
		'audio/mpeg' => 'mp3',
		'video/mpeg' => ['mpe', 'mpeg', 'mpg'],
		'audio/ogg' => ['oga', 'ogg', 'ogv'],
		'application/ogg' => 'ogx',
		'image/x-portable-bitmap' => 'pbm',
		'application/pdf' => 'pdf',
		'image/x-portable-graymap' => 'pgm',
		'image/png' => 'png',
		'image/x-portable-anymap' => 'pnm',
		'image/x-portable-pixmap' => 'ppm',
		'application/vnd.ms-powerpoint' => 'ppt',
		'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
		'application/x-rar-compressed' => 'rar',
		'image/x-cmu-raster' => 'ras',
		'application/rss+xml' => 'rss',
		'application/rtf' => 'rtf',
		'text/sgml' => ['sgm', 'sgml'],
		'image/svg+xml' => 'svg',
		'application/x-shockwave-flash' => 'swf',
		'application/x-tar' => 'tar',
		'image/tiff' => ['tif', 'tiff'],
		'application/x-bittorrent' => 'torrent',
		'application/x-font-ttf' => 'ttf',
		'audio/x-wav' => 'wav',
		'video/webm' => 'webm',
		'audio/x-ms-wma' => 'wma',
		'video/x-ms-wmv' => 'wmv',
		'application/x-font-woff' => 'woff',
		'application/wsdl+xml' => 'wsdl',
		'image/x-xbitmap' => 'xbm',
		'application/vnd.ms-excel' => 'xls',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
		'application/xml' => 'xml',
		'image/x-xpixmap' => 'xpm',
		'image/x-xwindowdump' => 'xwd',
		'text/yaml' => ['yaml', 'yml'],
		'application/zip' => 'zip',
		'application/x-zip-compressed' => 'zip',
	];

	$expectedExtension = $mimeToExtension[$mime] ?? null;

	if (is_null($expectedExtension)) {
		return false;
	} else {
		if (is_array($expectedExtension)) {
			return in_array($extension, $expectedExtension);
		} else {
			return $extension === $expectedExtension;
		}
	}
}

/**
 * Mag de op dit moment ingelogde gebruiker $permissie?
 *
 * Korte methode voor gebruik in smarty templates.
 *
 * @param string $permission
 * @param array|null $allowedAuthenticationMethods
 * @return bool
 */
function mag($permission, array $allowedAuthenticationMethods = null) {
    return LoginModel::mag($permission, $allowedAuthenticationMethods);
}

/**
 * Is $uid de op dit moment ingelogde account?
 *
 * @param string $uid
 * @return bool
 */
function is_ingelogd_account($uid) {
    return LoginModel::getUid() == $uid;
}

/**
 * @param Profiel $profiel
 * @param string $key
 * @param string $uitzondering Sommige commissie mogen wel dit veld zien.
 * @return bool
 */
function is_zichtbaar($profiel, $key, $uitzondering = 'P_LEDEN_MOD') {
    if (is_array($key)) {
        foreach ($key as $item) {
            if (!LidToestemmingModel::instance()->toestemming($profiel, $item, $uitzondering)) {
                return false;
            }
        }

        return true;
    }

    return LidToestemmingModel::instance()->toestemming($profiel, $key, $uitzondering);
}

function to_unix_path($path) {
	return str_replace(DIRECTORY_SEPARATOR, "/", $path);
}

function realpathunix($path) {
	return to_unix_path(realpath($path));
}