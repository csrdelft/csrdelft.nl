<?php

use CsrDelft\model\fiscaat\CiviBestellingModel;
use CsrDelft\model\fiscaat\pin\PinTransactieMatcher;
use CsrDelft\model\fiscaat\pin\PinTransactieMatchModel;
use CsrDelft\model\fiscaat\pin\PinTransactieModel;

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

$pintransacties = PinTransactieModel::instance()->getPinTransactieInMoment($from, $to);
$pinbestellingen = CiviBestellingModel::instance()->getPinBestellingInMoment($from, $to);

echo sprintf("We kijken van %s tot %s.\n", $from, $to);

echo sprintf("Er zijn %d pin transacties\nEr zijn %d pin bestellingen.\n", count($pintransacties), count($pinbestellingen));

try {
	PinTransactieMatcher::clean($pintransacties, $pinbestellingen);

	$matches = PinTransactieMatcher::match($pintransacties, $pinbestellingen);

	echo "De volgende matches zijn gevonden." . PHP_EOL;
	echo PinTransactieMatcher::genereerReport($matches);

	PinTransactieMatchModel::instance()->createAll($matches);
} catch (Exception $e) {
	echo "er ging iets mis";
	print_r($e->getTraceAsString());
}
