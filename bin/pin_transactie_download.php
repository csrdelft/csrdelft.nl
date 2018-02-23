<?php
/**
 * pin_transactie_download.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 06/09/2017
 */

use CsrDelft\model\fiscaat\pin\PinTransactieDownloader;
use CsrDelft\model\fiscaat\pin\PinTransactieModel;

/**
 * Date constants.
 */
const DATE_FORMAT = 'Y-m-d';
const DURATION_DAY_IN_SECONDS = 86400;

require_once __DIR__ . '/../lib/configuratie.include.php';

if (isset($argv[1])) {
	$moment = strtotime($argv[1]);
} else {
	$moment = time() - DURATION_DAY_IN_SECONDS;
}

$from = date(DATE_FORMAT . ' 12:00:00', $moment);
$to = date(DATE_FORMAT . ' 12:00:00', $moment + DURATION_DAY_IN_SECONDS);

// Verwijder eerdere download.
$vorigePinTransacties = PinTransactieModel::instance()->getPinTransactieInMoment($from, $to);

foreach ($vorigePinTransacties as $pinTransactie) {
	PinTransactieModel::instance()->delete($pinTransactie);
}

$settings = parse_ini_file(__DIR__ . '/../etc/pin_transactie_download.ini');

// Download pintransacties en sla op in DB.
$pintransacties = PinTransactieDownloader::download($settings, $moment);

echo sprintf("Er zijn %d pin transacties gedownload.\n", count($pintransacties));
