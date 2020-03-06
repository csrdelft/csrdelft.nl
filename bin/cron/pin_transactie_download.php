<?php
/**
 * pin_transactie_download.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 06/09/2017
 */

use CsrDelft\model\entity\Mail;
use CsrDelft\model\fiscaat\CiviBestellingModel;
use CsrDelft\model\fiscaat\pin\PinTransactieDownloader;
use CsrDelft\model\fiscaat\pin\PinTransactieMatcher;
use CsrDelft\model\fiscaat\pin\PinTransactieMatchModel;
use CsrDelft\model\fiscaat\pin\PinTransactieModel;

/**
 * Date constants.
 */
const DATE_FORMAT = 'Y-m-d';
const DURATION_DAY_IN_SECONDS = 86400;

require_once __DIR__ . '/../../lib/configuratie.include.php';

if (isset($argv[1])) {
	$moment = strtotime($argv[1]);
	$interactive = true;
} else {
	$moment = time() - DURATION_DAY_IN_SECONDS;
	$interactive = false;
}

$from = date(DATE_FORMAT . ' 12:00:00', $moment - DURATION_DAY_IN_SECONDS);
$to = date(DATE_FORMAT . ' 12:00:00', $moment);

// Verwijder eerdere download.
$vorigePinTransacties = PinTransactieModel::instance()->getPinTransactieInMoment($from, $to);

foreach ($vorigePinTransacties as $pinTransactie) {
    $matches = PinTransactieMatchModel::instance()->find('transactie_id = ?', [$pinTransactie->id])->fetchAll();

    foreach ($matches as $match) {
        PinTransactieMatchModel::instance()->delete($match);
    }
}

foreach ($vorigePinTransacties as $pinTransactie) {
	PinTransactieModel::instance()->delete($pinTransactie);
}

$settings = [
	PinTransactieDownloader::SETTINGS_USERNAME => env('PIN_USERNAME'),
	PinTransactieDownloader::SETTINGS_PASSWORD => env('PIN_PASSWORD'),
	PinTransactieDownloader::SETTINGS_STORE => env('PIN_STORE'),
	PinTransactieDownloader::SETTINGS_URL => env('PIN_URL'),
];

// Download pintransacties en sla op in DB.
$pintransacties = PinTransactieDownloader::download($settings, $from);

// Haal pinbestellingen op.
$pinbestellingen = CiviBestellingModel::instance()->getPinBestellingInMoment($from, $to);

try {
	$matcher = \CsrDelft\common\ContainerFacade::getContainer()->get(PinTransactieMatcher::class);
	$matcher->setPinTransacties($pintransacties);
	$matcher->setPinBestellingen($pinbestellingen);

	$matcher->clean();
	$matcher->match();
	$matcher->save();


	if ($matcher->bevatFouten()) {
		$report = $matcher->genereerReport();

		$body = <<<MAIL
Beste am. Fiscus,

Zojuist zijn de pin transacties en bestellingen tussen {$from} en {$to} geanalyseerd.

De volgende fouten zijn gevonden.

{$report}

Met vriendelijke groet,

namense de PubCie,
Feut
MAIL;

		if ($interactive) {
			echo $body;
			echo "\n\nDe email is niet verzonden, want de sessie is in interactieve modus.\n";
			echo sprintf("Er zijn %d pin transacties gedownload.\n", count($pintransacties));

		} else {
			$mail = new Mail([env('PIN_MONITORING_EMAIL') => 'Pin Transactie Monitoring'], '[CiviSaldo] Pin transactie fouten gevonden.', $body);
			$mail->send();
		}
	}

} catch (Exception $e) {
	if ($interactive) {
		echo $e->getMessage() . "\n";
		echo $e->getTraceAsString();
	} else {
		// Throw naar shutdownhandler.
		/** @noinspection PhpUnhandledExceptionInspection */
		throw $e;
	}
}

