<?php
/**
 * sponsorkliks_affiliate_download.php
 *
 * @author J. Rijsdijk <jorairijsdijk@gmail.com>
 * @date 26/10/2017
 */

use CsrDelft\common\Ini;

/**
 * Settings constants.
 */
const SETTINGS_CLUBID = 'clubid';
const SETTINGS_SL_HOST = 'sl_host';
const SETTINGS_UA = 'useragent';

/**
 * Url constants.
 */

require_once __DIR__ . '/../lib/configuratie.include.php';

//Steps
$settings = Ini::lees(Ini::SPONSOR_AFFILIATES_DOWNLOAD);

$SL_HOST = $settings[SETTINGS_SL_HOST];
$PAGE_URL = $SL_HOST . '/api/?call=webshops_club_extension&club=';

$clubId = $settings[SETTINGS_CLUBID];
$scrapeUrl = $PAGE_URL . $clubId;

//1. GET JSON
$result = curl_request($scrapeUrl, [CURLOPT_USERAGENT => $settings[SETTINGS_UA]]);
$webshops = json_decode($result)->webshops;

//3. Follow links to final destination
$data = ["club_id" => $clubId];
$affiliates = [];
$amount = 0;
foreach ($webshops as $webshop) {
    if ($webshop->extension == "0") {
        continue;
    }

    preg_match('/shop_id=(\d+)/', $webshop->link, $shopId);
	$entry = [
	    "shop_name" => $webshop->name_short,
        "shop_name_long" => $webshop->name_long,
        "shop_category" => $webshop->category,
        "shop_id" => $shopId[1],
        "shop_price" => $webshop->commission_gross,
        "shop_description" => $webshop->description,
        "shop_logo" => $webshop->logo_120x60
    ];

	$host = $webshop->orig_url;
	if (array_key_exists($host, $data['affiliates'])) {
	    $affiliates[$host][] = $entry;
    } else {
        $affiliates[$host] = [$entry];
    }

	$amount++;
}

// Store affiliates map in (soon to be JSON) output data
$data["affiliates"] = $affiliates;

//4. Save results to sponsorlinks.json in data folder (overwriting existing)
$json = json_encode($data);
$outputFile = fopen(DATA_PATH . 'sponsorlinks.json', 'w');
fwrite($outputFile, $json);
fclose($outputFile);

