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
const PAGE_URL = SK_HOST . '/products/shops.php?show=all&club=';

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

//3. Follow links to final destination
$data = ["club_id" => $clubId];
$affiliates = [];
$amount = 0;
$shop_desc_url = SK_HOST . "/products/shop.php?club=$clubId&id=";
foreach ($affiliateBoxes as $affiliate) {
	$element = $xpath->query('div/a[@class="product-name orderlink"]', $affiliate)->item(0);
	$shopName = trim($element->textContent);
    $href = $element->getAttribute('href');
    $matches = [];
    preg_match('/shop_id=([0-9]+)&/', $href, $matches);
    $shopId = $matches[1];
    $shopPrice = trim($xpath->query('div/span[@class="product-price"]', $affiliate)->item(0)->textContent);

    // Get long shop price text
    $shop_xpath = init_xpath(curl_request($shop_desc_url . $shopId));
    $productDetail = $shop_xpath->query('//div[@class="ibox product-detail"]')->item(0);
    $shopPriceLong = $shop_xpath->query('//h2', $productDetail)->item(2)->textContent;
    $shopDesc = trim($shop_xpath->query('//h4', $productDetail)->item(0)->nextSibling->textContent);

	$sponsorUrl = curl_follow_location(SK_HOST . $href);
	$host = parse_url($sponsorUrl, PHP_URL_HOST);
	$entry = [
	    "shop_name" => $shopName,
        "shop_id" => $shopId,
        "shop_price" => $shopPrice,
        "shop_price_long" => $shopPriceLong,
        "shop_description" => $shopDesc
    ];

	if (array_key_exists($host, $data['affiliates'])) {
	    $affiliates[$host][] = $entry;
    } else {
        $affiliates[$host] = [$entry];
    }

	$amount++;
}

// Delete failed ad.zanox.com links
unset($affiliates['ad.zanox.com']);
// Delete outdated daisycon affiliate network links
unset($affiliates['www.manymorestores.com']);
// Delete failed static.tradetracker.net links
unset($affiliates['static.tradetracker.net']);
// Delete failed/old www.paypro.nl links
unset($affiliates['www.paypro.nl']);
// Delete offers that don't leave sponsorkliks.com
unset($affiliates['www.sponsorkliks.com']);

// Store affiliates map in (soon to be JSON) output data
$data["affiliates"] = $affiliates;

//4. Save results to sponsorkliks.json in data folder (overwriting existing)
$json = json_encode($data);
$outputFile = fopen(DATA_PATH . 'sponsorkliks.json', 'w');
fwrite($outputFile, $json);
fclose($outputFile);

