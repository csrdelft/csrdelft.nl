<?php
/**
 * pin_transactie_download.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 06/09/2017
 */
use CsrDelft\model\entity\fiscaat\PinTransactie;
use CsrDelft\model\entity\Mail;
use CsrDelft\model\fiscaat\PinTransactieModel;
use function CsrDelft\url2absolute;

/**
 * Post Field constants.
 */
const POST_FIELD_LOGIN_USERNAME = 'login.username';
const POST_FIELD_LOGIN_PASSWORD = 'login.password';
const POST_FIELD_PERIOD_FROM_DATE_DATE = 'period.from_date:date';
const POST_FIELD_PERIOD_FROM_DATE_HOURS = 'period.from_date:hours';
const POST_FIELD_PERIOD_FROM_DATE_MINUTES = 'period.from_date:minutes';
const POST_FIELD_PERIOD_DURATION = 'period.duration';
const POST_FIELD_STORE = 'select.store.container:select.store';

/**
 * Settings constants.
 */
const SETTINGS_USERNAME = 'username';
const SETTINGS_PASSWORD = 'password';
const SETTINGS_STORE = 'store';
const SETTINGS_URL = 'url';

/**
 * Date constants.
 */
const DATETIME_FORMAT = 'Y-m-d H:i:s';
const DATE_FORMAT = 'Y-m-d';
const DATE_FORMAT_ONLINE = 'd-m-Y';
const DATE_START_HOURS = '12';
const DATE_START_MINUTES = '00';
const DURATION_DAY = '0';
const DURATION_DAY_IN_SECONDS = 86400;

/**
 * Url constants.
 */
const RELATIVE_URL_LOGIN = '../nl/login/wicket:interface/:0:form::IFormSubmitListener::';
const RELATIVE_URL_REPORT = '../nl/report';

require_once __DIR__ . '/../lib/configuratie.include.php';

//Steps
//1. Login
$settings = parse_ini_file(__DIR__ . '/../etc/pin_transactie_download.ini');

$baseUrl = $settings[SETTINGS_URL];

$postFields = [
	POST_FIELD_LOGIN_USERNAME => $settings[SETTINGS_USERNAME],
	POST_FIELD_LOGIN_PASSWORD => $settings[SETTINGS_PASSWORD],
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, url2absolute($baseUrl, RELATIVE_URL_LOGIN));
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// get headers too with this line
curl_setopt($ch, CURLOPT_HEADER, true);
$result = curl_exec($ch);

//2. Parse session cookie from response
preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
$cookies = array();
foreach ($matches[1] as $item) {
	parse_str($item, $cookie);
	$cookies = array_merge($cookies, $cookie);
}

$sessionCookie = 'JSESSIONID=' . $cookies['JSESSIONID'];

//3. GET report overview
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, url2absolute($baseUrl, RELATIVE_URL_REPORT));
curl_setopt($ch, CURLOPT_COOKIE,  $sessionCookie);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);

//4. Retrieve Merchant Transactions Url #article-content .report a[title=Merchant transactions]@href
$xml = new DOMDocument();
$xml->loadHTML($result);
$xpath = new DOMXPath($xml);
$merchantTransactionsUrl = $xpath->query('//a[@title = "Merchant transactions"]/@href')->item(0)->nodeValue;

//5. GET Merchant Transactions Url
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, url2absolute($baseUrl, $merchantTransactionsUrl));
curl_setopt($ch, CURLOPT_COOKIE, $sessionCookie);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);

//6. Retrieve Search Url: Only form tag -> action
preg_match('/action="(.*?)"/', $result, $searchMatches);
$searchUrl = $searchMatches[1];

//7. Do call to search with correct date
$postFields = [
	POST_FIELD_PERIOD_FROM_DATE_DATE => date(DATE_FORMAT_ONLINE, time() - DURATION_DAY_IN_SECONDS),
	POST_FIELD_PERIOD_FROM_DATE_HOURS => DATE_START_HOURS,
	POST_FIELD_PERIOD_FROM_DATE_MINUTES => DATE_START_MINUTES,
	POST_FIELD_PERIOD_DURATION => DURATION_DAY,
	POST_FIELD_STORE => $settings[SETTINGS_STORE],
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, url2absolute($baseUrl, $searchUrl));
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
curl_setopt($ch, CURLOPT_COOKIE, $sessionCookie);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$result = curl_exec($ch);

//8. Parse html and create PinTransactie
$xml = new DOMDocument();
$xml->loadHTML($result);
$xpath = new DOMXPath($xml);

$tableRow = $xpath->query('//table[@class="table"]/tbody/tr');

$unmatched = 0;
foreach ($tableRow as $row) {
	$labels = $xpath->query('td/label', $row);

	$pinTransactie = new PinTransactie();
	$pinTransactie->datetime = date(DATETIME_FORMAT, strtotime($labels->item(0)->nodeValue));
	$pinTransactie->brand = $labels->item(1)->nodeValue;
	$pinTransactie->merchant = $labels->item(2)->nodeValue;
	$pinTransactie->store = $labels->item(3)->nodeValue;
	$pinTransactie->terminal = $labels->item(4)->nodeValue;
	$pinTransactie->TID = $labels->item(5)->nodeValue;
	$pinTransactie->MID = $labels->item(6)->nodeValue;
	$pinTransactie->ref = $labels->item(7)->nodeValue;
	$pinTransactie->type = $labels->item(8)->nodeValue;
	$pinTransactie->amount = $labels->item(9)->nodeValue;
	$pinTransactie->AUTRSP = $labels->item(10)->nodeValue;
	$pinTransactie->STAN = $labels->item(11)->nodeValue;

	$pinTransactie->id = PinTransactieModel::instance()->create($pinTransactie);

	if (!PinTransactieModel::instance()->match($pinTransactie)) {
		$unmatched++;
	}
}

$period_start = date(DATE_FORMAT_ONLINE, time() - DURATION_DAY_IN_SECONDS);
$period_end = date(DATE_FORMAT_ONLINE, time());

if ($unmatched !== 0) {
	$body = <<<MAIL
Beste am. Fiscus,

Zojuist zijn er {$tableRow->length} pin transacties gedownload voor de periode {$period_start} 12:00 tot {$period_end} 12:00. 

Van deze pin transacties is voor {$unmatched} geen overeenkomende bestelling gevonden in het systeem. 

Met vriendelijke groet,

namens de PubCie, 
Feut
MAIL;

	$mail = new Mail([$settings['monitoring_email'] => 'Pin Transactie Monitoring'], '[CiviSaldo] Onbekende pintransactie gevonden.', $body);
	$mail->send();
}

$from = date(DATE_FORMAT . ' 12:00:00', time() - DURATION_DAY_IN_SECONDS);
$to = date(DATE_FORMAT . ' 12:00:00', time());

$unmatched = PinTransactieModel::instance()->getUnmatched($from, $to);

$unmatchedList = implode(', ', $unmatched);

if (count($unmatched) !== 0) {
	$body = <<<MAIL
Beste am. Fiscus,

Er zijn pin bestellingen in het systeem gevonden waarvoor geen pin transactie is gevonden. Het gaat om de volgende bestellingen:

{$unmatchedList}.

Met vriendelijke groet,

namense de PubCie,
Feut
MAIL;

	$mail = new Mail([$settings['monitoring_email'] => 'Pin Transactie Monitoring'], '[CiviSaldo] Geen pin transactie gevonden voor bestelling.', $body);
	$mail->send();
}


