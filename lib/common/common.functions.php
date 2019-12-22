<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# common.functions.php
# -------------------------------------------------------------------
use CsrDelft\common\CsrException;
use CsrDelft\common\ShutdownHandler;
use CsrDelft\model\entity\profiel\Profiel;
use CsrDelft\model\instellingen\InstellingenModel;
use CsrDelft\model\instellingen\LidInstellingenModel;
use CsrDelft\model\instellingen\LidToestemmingModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\Persistence\DatabaseAdmin;
use CsrDelft\service\CsrfService;
use CsrDelft\view\formulier\CsrfField;
use CsrDelft\view\Icon;
use CsrDelft\view\ToResponse;
use Symfony\Component\HttpFoundation\Response;

define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');

/**
 * @source http://stackoverflow.com/questions/834303/php-startswith-and-endswith-functions
 * @param string $haystack
 * @param string $needle
 *
 * @return boolean
 */
function startsWith($haystack, $needle) {
	return strval($needle) === "" || strpos($haystack, strval($needle)) === 0;
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
 * @param array|PDOStatement $in
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
 * @param array|PDOStatement $in
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
 * Set cookie with token to automatically login.
 *
 * @param string $token
 */
function setRememberCookie($token) {
	if ($token == null) {
		unset($_COOKIE['remember']);
		setcookie('remember', null, -1, '/', CSR_DOMAIN, FORCE_HTTPS, true);
	} else {
		setcookie('remember', $token, time() + (int)instelling('beveiliging', 'remember_login_seconds'), '/', CSR_DOMAIN, FORCE_HTTPS, true);
	}
}

/**
 * @return int
 */
function getSessionMaxLifeTime() {
	$lifetime = (int)instelling('beveiliging', 'session_lifetime_seconds');
	// Sync lifetime of FS based PHP session with DB based C.S.R. session
	$gc = (int)ini_get('session.gc_maxlifetime');
	if ($gc > 0 && $gc < $lifetime) {
		$lifetime = $gc;
	}
	return $lifetime;
}

/**
 * Invokes a client page (re)load the url.
 *
 * @param string $url
 * @param boolean $refresh allow a refresh; redirect to CSR_ROOT otherwise
 */
function redirect($url = null, $refresh = true) {
	if (empty($url) || $url === null) {
		$url = REQUEST_URI;
	}
	if (!$refresh && $url == REQUEST_URI) {
		$url = CSR_ROOT;
	}
	if (!startsWith($url, CSR_ROOT)) {
		if (preg_match("/^[?#\/]/", $url) === 1) {
			$url = CSR_ROOT . $url;
		} else {
			$url = CSR_ROOT;
		}
	}
	header('location: ' . $url);
	exit;
}

function redirect_via_login($url) {
	redirect(CSR_ROOT . "/login?redirect=" . urlencode($url));
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
 * @param $length
 * @return string
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
	return preg_match('/^(?:[a-z0-9 \-_()éê]|\.(?!\.))+$/iD', $name);
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
	return preg_match("/^[a-zA-Z0-9!#$%&'*+=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&'*+=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+(?:[a-zA-Z]{2,})\b$/", $email);
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
	if ($url && (url_like($url) || url_like(CSR_ROOT . $url))) {
		if (startsWith($url, 'http://') || startsWith($url, 'https://')) {
			$extern = 'target="_blank"';
		} else {
			$extern = '';
		}
		$result = '<a href="' . $url . '" title="' . $url . '" ' . $extern . '>' . $label . '</a>';
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
	if (!is_numeric($jaar) || strlen($jaar) != 4) {
		return false;
	}
	$maand = $delen[1];
	if (!is_numeric($maand) || strlen($maand) != 2) {
		return false;
	}
	$dag = substr($delen[2], 0, 2); // Alleen de eerste twee karakters pakken.
	if (!is_numeric($dag) || strlen($dag) != 2) {
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
	if (DEBUG || LoginModel::mag(P_ADMIN) || LoginModel::instance()->isSued()) {
		echo '<pre class="' . $cssID . '">' . print_r($sString, true) . '</pre>';
	}
}

function reldate($datum) {
	if ($datum instanceof DateTime) {
		$moment = $datum->getTimestamp();
	} else {
		$moment = strtotime($datum);
	}

	if (date('Y-m-d') == date('Y-m-d', $moment)) {
		$return = 'vandaag om ' . strftime('%H:%M', $moment);
	} elseif (date('Y-m-d', $moment) == date('Y-m-d', strtotime('1 day ago'))) {
		$return = 'gisteren om ' . strftime('%H:%M', $moment);
	} else {
		$return = strftime('%A %e %B %Y om %H:%M', $moment); // php-bug: %e does not work on Windows
	}
	return '<time class="timeago" datetime="' . date('Y-m-d\TG:i:sO', $moment) . '">' . $return . '</time>'; // ISO8601
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
 * @param $src_image
 * @param $dest_image
 * @param int $thumb_size
 * @param int $jpg_quality
 * @return bool
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
	return round($size, 1) . $units[$i];
}

/**
 * This function transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in this case)
 *
 * @source http://stackoverflow.com/a/22500394
 * @param $sSize
 * @return false|int|string
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
	$enableDebug = filter_input(INPUT_GET, 'debug') !== null;
	if ($enableDebug && (DEBUG || (LoginModel::mag(P_ADMIN) || LoginModel::instance()->isSued()))) {
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
		$debug .= '<hr />SESSION<hr />' . htmlspecialchars(print_r($_SESSION, true));
	}
	if ($server) {
		$debug .= '<hr />SERVER<hr />' . htmlspecialchars(print_r($_SERVER, true));
	}
	if ($sql) {
		$debug .= '<hr />SQL<hr />' . htmlspecialchars(print_r(array("Admin" => DatabaseAdmin::instance()->getQueries(), "PDO" => Database::instance()->getQueries()), true));
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
 * @param string $msg
 * @param int $lvl
 */
function setMelding(string $msg, int $lvl) {
	$levels[-1] = 'danger';
	$levels[0] = 'info';
	$levels[1] = 'success';
	$levels[2] = 'warning';
	$msg = trim($msg);
	if (!empty($msg) && ($lvl === -1 || $lvl === 0 || $lvl === 1 || $lvl === 2)) {
		if (!isset($_SESSION['melding'])) {
			$_SESSION['melding'] = array();
		}
		// gooit verouderde gegevens weg
		if (is_string($_SESSION['melding'])) {
			$_SESSION['melding'] = array();
		}
		$_SESSION['melding'][] = array('lvl' => $levels[$lvl], 'msg' => $msg);
	}
}

/**
 * Geeft berichten weer die opgeslagen zijn in de sessie met met setMelding($msg, $lvl)
 *
 * @return string html van melding(en) of lege string
 */
function getMelding() {
	if (isset($_SESSION['melding']) && is_array($_SESSION['melding'])) {
		$melding = '';
		foreach ($_SESSION['melding'] as $msg) {
			$melding .= formatMelding($msg['msg'], $msg['lvl']);
		}
		// de melding maar één keer tonen.
		unset($_SESSION['melding']);
	} else {
		$melding = '';
	}

	return '<div id="melding">' . $melding . '</div>';
}

/**
 * @param string $msg
 * @param string $lvl
 * @return string
 */
function formatMelding(string $msg, string $lvl) {
	$icon = Icon::getTag('alert-' . $lvl);

	return <<<HTML
<div class="alert alert-${lvl}">
${icon}${msg}
</div>
HTML;

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
		return (new ReflectionClass($className))->getShortName();
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
 * @return DOMXPath The xpath object
 */
function init_xpath($html) {
	$xml = new DOMDocument();
	$xml->loadHTML($html);
	return new DOMXPath($xml);
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
		'audio/mp3' => 'mp3',
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
 * Korte methode voor gebruik in Blade templates.
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
 * @param string|string[] $key
 * @param string $cat
 * @param string $uitzondering Sommige commissie mogen wel dit veld zien.
 * @return bool
 */
function is_zichtbaar($profiel, $key, $cat = 'profiel', $uitzondering = P_LEDEN_MOD) {
	if (is_array($key)) {
		foreach ($key as $item) {
			if (!LidToestemmingModel::instance()->toestemming($profiel, $item, $cat, $uitzondering)) {
				return false;
			}
		}

		return true;
	}

	return LidToestemmingModel::instance()->toestemming($profiel, $key, $cat, $uitzondering);
}

function lid_instelling($module, $key) {
	return LidInstellingenModel::instance()->getValue($module, $key);
}

function instelling($module, $key) {
	return InstellingenModel::instance()->getValue($module, $key);
}

function to_unix_path($path) {
	return str_replace(DIRECTORY_SEPARATOR, "/", $path);
}

/**
 * Combines two parts of a file path safely, meaning that the resulting path will be inside $folder.
 * If directory traversal is applied using ../ et cetera, making the path no longer be inside $folder, null is returned;
 * @param $folder
 * @param $subpath
 * @return string|null
 */
function safe_combine_path($folder, $subpath) {
	if ($folder == null || $subpath == null) {
		return null;
	}
	$combined = $folder;
	if (!endsWith($combined, '/')) {
		$combined .= '/';
	}
	$combined .= $subpath;
	if (!startsWith(realpath($combined), realpath($folder))) {
		return null;
	}
	return $combined;
}

function realpathunix($path) {
	return to_unix_path(realpath($path));
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
function uniqid_safe($prefix = "") {
	return str_replace('.', '_', uniqid($prefix, true));
}

/**
 * Versie van shuffle die niet de originele array veranderd en wel een waarde terug geeft.
 *
 * @param array $arr
 * @return array
 */
function array_shuffle(array $arr) {
	shuffle($arr);

	return $arr;
}

$configCache = [];

function sql_contains($field) {
	return "%$field%";
}

function printCsrfField($path = '', $method = 'post') {
	(new CsrfField(CsrfService::instance()->generateToken($path, $method)))->view();
}

function csrfMetaTag() {
	$token = CsrfService::instance()->generateToken('', 'POST');
	return '<meta property="X-CSRF-ID" content="'. htmlentities($token->getId()) .'" /><meta property="X-CSRF-VALUE" content="'. htmlentities($token->getValue()) .'" />';
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
	function array_key_first($array) {
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
	function array_key_last($array) {
		$key = NULL;

		if ( is_array( $array ) ) {

			end( $array );
			$key = key( $array );
		}

		return $key;
	}
}

function delTree($dir) {
	$files = array_diff(scandir($dir), array('.','..'));
	foreach ($files as $file) {
		(is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
	}
	return rmdir($dir);
}

function vue_encode($object) {
	return htmlspecialchars(json_encode($object));
}

function join_paths(...$args) {
	$paths = [];

	foreach ($args as $arg) {
		if ($arg !== '') { $paths[] = $arg; }
	}

	return preg_replace('#/+#','/',join('/', $paths));
}

/**
 * Checks if $path exists in $prefix and if it is still inside $prefix.
 *
 * @param $prefix
 * @param $path
 * @return bool
 */
function path_valid($prefix, $path) {
	return startsWith(realpathunix(join_paths($prefix, $path)), realpathunix($prefix));
}

function triggerExceptionAsWarning(Exception $e) {
	ShutdownHandler::triggerSlackMessage($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine(), true);
}

/**
 * @param \Traversable|array
 * @return array
 */
function as_array($value) {
	if (is_array($value)) {
		return $value;
	} else if ($value instanceof \Traversable) {
		return iterator_to_array($value);
	}
	throw new CsrException("Geen array of iterable");
}
