<?php
/**
 * sponsorkliks_affiliate_download.php
 *
 * @author J. Rijsdijk <jorairijsdijk@gmail.com>
 * @date 26/10/2017
 */

use function CsrDelft\curl_follow_location;
use function CsrDelft\curl_request;
use function CsrDelft\init_xpath;

/**
 * Settings constants.
 */
const SETTINGS_CLUBID = 'clubid';

/**
 * Url constants.
 */
const SK_HOST = 'https://www.sponsorkliks.com';
const PAGE_URL = SK_HOST . '/products/shops.php?club=';

require_once __DIR__ . '/../lib/configuratie.include.php';

//Steps
$settings = parse_ini_file(__DIR__ . '/../etc/sponsorkliks_affiliates_download.ini');

$clubId = $settings[SETTINGS_CLUBID];
$scrapeUrl = PAGE_URL . $clubId;

//1. GET affiliates pagina
$result = curl_request($scrapeUrl);

//2. Retrieve affiliate boxes
$xpath = init_xpath($result);
$affiliateBoxes = $xpath->query('//div[@class = "ibox-content product-box"]');
$affiliates = [];

//3. Follow links to final destination
foreach ($affiliateBoxes as $affiliate) {
	$element = $xpath->query('div/a[@class="product-name orderlink"]', $affiliate)->item(0);
	$href = $element->getAttribute('href');

	$sponsorUrl = curl_follow_location(SK_HOST . $href);
	$affiliates[parse_url($sponsorUrl, PHP_URL_HOST)] = ["name" => $element->nodeValue, "link" => $href];
}

//4. Save results to sponsorkliks.json in data folder (overwriting existing)
$json = json_encode($affiliates);
$outputFile = fopen(DATA_PATH . 'sponsorkliks.json', 'w');
fwrite($outputFile, $json);
fclose($outputFile);

