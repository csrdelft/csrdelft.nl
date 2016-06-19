<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# common.functions.php
# -------------------------------------------------------------------

/**
 * PDO does a stringcast (false = '') and MySql uses tinyint for booleans so expects 0/1
 */
function werkomheen_pdo_bool($value) {
	if (is_bool($value)) {
		$value = (int) $value;
	}
	return $value;
}

/**
 * @source http://stackoverflow.com/questions/834303/php-startswith-and-endswith-functions
 * @param string $haystack
 * @param string $needle
 * @return boolean
 */
function startsWith($haystack, $needle) {
	return $needle === "" || strpos($haystack, $needle) === 0;
}

/**
 * @source http://stackoverflow.com/questions/834303/php-startswith-and-endswith-functions
 * @param string $haystack
 * @param string $needle
 * @return boolean
 */
function endsWith($haystack, $needle) {
	return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}

/**
 * @source http://stackoverflow.com/a/3654335
 * @param type $array
 * @return type
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
 * Invokes a client page (re)load the url.
 * 
 * @param string $url
 */
function redirect($url = null, $refresh = true) {
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

/**
 * rawurlencode() met uitzondering van slashes.
 * 
 * @param string $url
 * @return string
 */
function direncode($url) {
	return str_replace('%2F', '/', rawurlencode($url));
}

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
	$codeAlphabet.= 'abcdefghijklmnopqrstuvwxyz';
	$codeAlphabet.= '0123456789';
	for ($i = 0; $i < $length; $i++) {
		$token .= $codeAlphabet[crypto_rand_secure(0, strlen($codeAlphabet))];
	}
	return $token;
}

function crypto_rand_secure($min, $max) {
	$range = $max - $min;
	if ($range < 0) {
		return $min; // not so random...
	}
	$log = log($range, 2);
	$bytes = (int) ($log / 8) + 1; // length in bytes
	$bits = (int) $log + 1; // length in bits
	$filter = (int) (1 << $bits) - 1; // set all lower bits to 1
	do {
		$rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
		$rnd = $rnd & $filter; // discard irrelevant bits
	} while ($rnd >= $range);
	return $min + $rnd;
}

function valid_date($date, $format = 'Y-m-d H:i:s') {
	$d = DateTime::createFromFormat($format, $date);
	return $d && $d->format($format) == $date;
}

function valid_filename($name) {
	return preg_match('/^(?:[a-z0-9 \-_\(\)é]|\.(?!\.))+$/iD', $name);
}

/**
 * @source http://www.regular-expressions.info/email.html
 */
function email_like($email) {
	if (empty($email)) {
		return false;
	}
	return preg_match("/^[a-zA-Z0-9!#$%&'\*\+=\?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&'\*\+=\?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+(?:[a-zA-Z]{2,})\b$/", $email);
}

/**
 * @source https://mathiasbynens.be/demo/url-regex
 */
function url_like($url) {
	if (empty($url)) {
		return false;
	}
	return preg_match('_^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:/[^\s]*)?$_iuS', $url);
}

function external_url($url, $label) {
	$url = filter_var($url, FILTER_SANITIZE_URL);
	if ($url AND ( url_like($url) OR url_like(CSR_ROOT . $url) )) {
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
	return checkdate((int) $maand, (int) $dag, (int) $jaar);
}

/**
 * print_r een variabele met <pre>-tags eromheen.
 * 
 * @param string $sString
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
 * @param 	string $sNamen string met namen en/of uids op nieuwe regels en/of gescheiden door komma's
 * @param   array|string $filter zoekfilter voor LidZoeker::zoekLeden, toegestane input: '', 'leden', 'oudleden' of array met stati
 * @return 	bool false bij geen matches
 * 			of een array met per zoekterm een entry met een unieke uid en naam òf een array met naamopties.
 * Voorbeeld:
 * Input: $sNamen = 'Lid, Klaassen'
 * Output: Array(
  [0] => Array (
  [naamOpties] => Array (
  [0] => Array (
  [uid] => 4444
  [naam] => Oud Lid
  )
  [1] => Array (
  [uid] => x101
  [naam] => Jan Lid
  )
  [2] => Array (
  ...
  )
  )
  [1] => Array (
  [uid] => 0431
  [naam] => Jan Klaassen
  )
  )
 */
function namen2uid($sNamen, $filter = 'leden') {
	$return = array();
	$sNamen = trim($sNamen);
	$sNamen = str_replace(array(', ', "\r\n", "\n"), ',', $sNamen);
	$aNamen = explode(',', $sNamen);
	$return = false;
	foreach ($aNamen as $sNaam) {
		$aNaamOpties = array();
		require_once 'lid/lidzoeker.class.php';
		$aZoekNamen = LidZoeker::zoekLeden($sNaam, 'naam', 'alle', 'achternaam', $filter, array('uid', 'voornaam', 'tussenvoegsel', 'achternaam'));
		if (count($aZoekNamen) == 1) {
			$naam = $aZoekNamen[0]['voornaam'] . ' ';
			if (trim($aZoekNamen[0]['tussenvoegsel']) != '') {
				$naam.=$aZoekNamen[0]['tussenvoegsel'] . ' ';
			}
			$naam.=$aZoekNamen[0]['achternaam'];
			$return[] = array('uid' => $aZoekNamen[0]['uid'], 'naam' => $naam);
		} elseif (count($aZoekNamen) == 0) {
			
		} else {
			//geen enkelvoudige match, dan een array teruggeven
			foreach ($aZoekNamen as $aZoekNaam) {
				$profiel = ProfielModel::get($aZoekNaam['uid']);
				$aNaamOpties[] = array(
					'uid'	 => $aZoekNaam['uid'],
					'naam'	 => $profiel->getNaam());
			}
			$return[]['naamOpties'] = $aNaamOpties;
		}
	}
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
	  } else */if (date('Y-m-d') == date('Y-m-d', $moment)) {
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
		case 'P': $iValue *= 1024;
		case 'T': $iValue *= 1024;
		case 'G': $iValue *= 1024;
		case 'M': $iValue *= 1024;
		case 'K': $iValue *= 1024;
		default:
			break;
	}
	return $iValue;
}

function getMaximumFileUploadSize() {
	return min(convertPHPSizeToBytes(ini_get('post_max_size')), convertPHPSizeToBytes(ini_get('upload_max_filesize')));
}

function printDebug() {
	if (DEBUG OR ( LoginModel::mag('P_ADMIN') OR LoginModel::instance()->isSued() )) {
		echo '<a id="mysql_debug_toggle" onclick="$(this).replaceWith($(\'#mysql_debug\').toggle());">DEBUG</a>';
		echo '<div id="mysql_debug" class="pre">' . getDebug() . '</div>';
	}
}

function getDebug($get = true, $post = true, $files = true, $cookie = true, $session = true, $server = true, $sql = true, $sqltrace = true) {
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
		require_once 'model/framework/DatabaseAdmin.singleton.php';
		$debug .= '<hr />SQL<hr />' . htmlspecialchars(print_r(array("Admin" => DatabaseAdmin::getQueries(), "PDO" => Database::getQueries(), "MySql" => MijnSqli::instance()->getQueries()), true));
	}
	if ($sqltrace) {
		$debug .= '<hr />SQL-backtrace<hr />' . htmlspecialchars(print_r(Database::getTrace(), true));
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
	if (!empty($msg) AND ( $lvl === -1 OR $lvl === 0 OR $lvl === 1 OR $lvl === 2 )) {
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
	if (isset($_SESSION['melding']) AND is_array($_SESSION['melding'])) {
		$sMelding = '<div id="melding">';
		$shown = array();
		foreach ($_SESSION['melding'] as $msg) {
			$hash = md5($msg['msg']);
			//if (isset($shown[$hash]))
			//	continue; // skip double messages
			$sMelding .= '<div class="alert alert-' . $msg['lvl'] . '">';
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
