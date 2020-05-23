<?php
/**
 * pin_transactie_download.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 06/09/2017
 */

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Mail;
use CsrDelft\repository\fiscaat\CiviBestellingRepository;
use CsrDelft\repository\pin\PinTransactieMatchRepository;
use CsrDelft\repository\pin\PinTransactieRepository;
use CsrDelft\service\pin\PinTransactieDownloader;
use CsrDelft\service\pin\PinTransactieMatcher;

/**
 * Date constants.
 */
const DURATION_DAY_IN_SECONDS = 86400;

require_once __DIR__ . '/../../lib/configuratie.include.php';

if (isset($argv[1])) {
	$moment = date_create_immutable($argv[1]);
	$interactive = true;
} else {
	$moment = date_create_immutable()->sub(new DateInterval('P1D'));
	$interactive = false;
}

$from = date_format_intl($moment->sub(new DateInterval('P1D')), DATE_FORMAT) . ' 12:00:00';
$to = date_format_intl($moment, DATE_FORMAT) . ' 12:00:00';

$container = ContainerFacade::getContainer();
$pinTransactieRepository = $container->get(PinTransactieRepository::class);
$pinTransactieMatchRepository = $container->get(PinTransactieMatchRepository::class);
$pinTransactieMatcher = $container->get(PinTransactieMatcher::class);
$pinTransactieDownloader = $container->get(PinTransactieDownloader::class);
$civiBestellingRepository = $container->get(CiviBestellingRepository::class);

// Verwijder eerdere download.
$vorigePinTransacties = $pinTransactieRepository->getPinTransactieInMoment($from, $to);

$pinTransactieMatchRepository->cleanByTransactieIds($vorigePinTransacties);
$pinTransactieRepository->clean($vorigePinTransacties);

// Download pintransacties en sla op in DB.
$pintransacties = $pinTransactieDownloader->download($from, env('PIN_URL'), env('PIN_STORE'), env('PIN_USERNAME'), env('PIN_PASSWORD'));

// Haal pinbestellingen op.
$pinbestellingen = $civiBestellingRepository->getPinBestellingInMoment($from, $to);

try {
	$pinTransactieMatcher->setPinTransacties($pintransacties);
	$pinTransactieMatcher->setPinBestellingen($pinbestellingen);

	$pinTransactieMatcher->clean();
	$pinTransactieMatcher->match();
	$pinTransactieMatcher->save();

	if ($pinTransactieMatcher->bevatFouten()) {
		$report = $pinTransactieMatcher->genereerReport();

		$body = <<<MAIL
Beste am. Fiscus,

Zojuist zijn de pin transacties en bestellingen tussen {$from} en {$to} geanalyseerd.

De volgende fouten zijn gevonden.

{$report}

Met vriendelijke groet,

namens de PubCie,
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
	} elseif($interactive) {
		echo "Er is niets gedownload!\n";
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

