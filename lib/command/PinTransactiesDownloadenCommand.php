<?php

namespace CsrDelft\command;

use CsrDelft\common\Mail;
use CsrDelft\repository\fiscaat\CiviBestellingRepository;
use CsrDelft\repository\pin\PinTransactieMatchRepository;
use CsrDelft\repository\pin\PinTransactieRepository;
use CsrDelft\service\pin\PinTransactieDownloader;
use CsrDelft\service\pin\PinTransactieMatcher;
use DateInterval;
use DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class PinTransactiesDownloadenCommand extends Command {
	/**
	 * @var PinTransactieRepository
	 */
	private $pinTransactieRepository;
	/**
	 * @var PinTransactieMatchRepository
	 */
	private $pinTransactieMatchRepository;
	/**
	 * @var PinTransactieMatcher
	 */
	private $pinTransactieMatcher;
	/**
	 * @var PinTransactieDownloader
	 */
	private $pinTransactieDownloader;
	/**
	 * @var CiviBestellingRepository
	 */
	private $civiBestellingRepository;
	/**
	 * @var bool
	 */
	private $interactive;

	public function __construct(
		PinTransactieRepository $pinTransactieRepository,
		PinTransactieMatchRepository $pinTransactieMatchRepository,
		PinTransactieMatcher $pinTransactieMatcher,
		PinTransactieDownloader $pinTransactieDownloader,
		CiviBestellingRepository $civiBestellingRepository
	) {
		parent::__construct(null);
		$this->pinTransactieRepository = $pinTransactieRepository;
		$this->pinTransactieMatchRepository = $pinTransactieMatchRepository;
		$this->pinTransactieMatcher = $pinTransactieMatcher;
		$this->pinTransactieDownloader = $pinTransactieDownloader;
		$this->civiBestellingRepository = $civiBestellingRepository;
	}

	protected function configure() {
		$this
			->setName('fiscaat:pintransacties:download')
			->setDescription('Download pintransacties van aangegeven periode en probeer te matchen met bestellingen.')
			->addArgument('vanaf', InputArgument::OPTIONAL, 'Vanaf welke datum wil je downloaden (jjjj-mm-dd)')
			->addArgument('tot', InputArgument::OPTIONAL, 'T/m welke datum wil je downloaden (jjjj-mm-dd)');

	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->interactive = $input->isInteractive() && !$input->getOption('no-interaction');

		if ($this->interactive) {
			$vanaf = DateTime::createFromFormat('Y-m-d', $input->getArgument('vanaf'));
			if (!$vanaf) {
				$output->writeln("Geef een geldige vanaf datum (jjjj-mm-dd)");
				return 1;
			}
			$tot = $input->getArgument('tot') ? DateTime::createFromFormat('Y-m-d', $input->getArgument('tot')) : clone $vanaf;
			if (!$tot) {
				$output->writeln("Geef een geldige tot datum (jjjj-mm-dd)");
				return 1;
			}

			if ($vanaf > $tot) {
				$output->writeln("Tot datum ligt voor vanaf datum");
				return 1;
			}

			$helper = $this->getHelper('question');
			$question = new ConfirmationQuestion("Weet je zeker dat je pin transacties wil downloaden? [Y/n]", true);
			if (!$helper->ask($input, $output, $question)) {
				return 1;
			}

			/** @var DateTime $cur */
			for ($cur = $vanaf; $cur <= $tot; $cur->add(new DateInterval('P1D'))) {
				$date = $cur->format("Y-m-d");
				$output->writeln("<info>" . $date . "</info>");
				$from = date_format_intl($cur, DATE_FORMAT) . ' 00:00:00';
				$to = date_format_intl($cur, DATE_FORMAT) . ' 23:59:59';
				$this->downloadDag($output, $from, $to);
			}
		} else {
			$moment = date_create_immutable()->sub(new DateInterval('P1D'));
			$from = date_format_intl($moment->sub(new DateInterval('P1D')), DATE_FORMAT) . ' 12:00:00';
			$to = date_format_intl($moment, DATE_FORMAT) . ' 12:00:00';
			$output->writeln("Downloaden van $from tot $to");
			$this->downloadDag($output, $from, $to);
		}

		return 0;
	}

	private function downloadDag(OutputInterface $output, $from, $to) {
		// Verwijder eerdere download.
		$vorigePinTransacties = $this->pinTransactieRepository->getPinTransactieInMoment($from, $to);

		$this->pinTransactieMatchRepository->cleanByTransactieIds($vorigePinTransacties);
		$this->pinTransactieRepository->clean($vorigePinTransacties);

		// Download pintransacties en sla op in DB.
		$pintransacties = $this->pinTransactieDownloader->download($from, $_ENV['PIN_URL'], $_ENV['PIN_STORE'], $_ENV['PIN_USERNAME'], $_ENV['PIN_PASSWORD']);

		// Haal pinbestellingen op.
		$pinbestellingen = $this->civiBestellingRepository->getPinBestellingInMoment($from, $to);

		$this->pinTransactieMatcher->setPinTransacties($pintransacties);
		$this->pinTransactieMatcher->setPinBestellingen($pinbestellingen);

		$this->pinTransactieMatcher->clean();
		$this->pinTransactieMatcher->match();
		$this->pinTransactieMatcher->save();

		if ($this->pinTransactieMatcher->bevatFouten()) {
			$report = $this->pinTransactieMatcher->genereerReport();

			$body = <<<MAIL
Beste am. Fiscus,

Zojuist zijn de pin transacties en bestellingen tussen {$from} en {$to} geanalyseerd.

De volgende fouten zijn gevonden.

{$report}

Met vriendelijke groet,

namens de PubCie,
Feut
MAIL;

			if ($this->interactive) {
				$output->writeln($body);
				$output->writeln("De mail is niet verzonden, want de sessie is in interactieve modus.");
				$output->writeln(sprintf("Er zijn %d pin transacties gedownload.", count($pintransacties)));

			} else {
				$mail = new Mail([$_ENV['PIN_MONITORING_EMAIL'] => 'Pin Transactie Monitoring'], '[CiviSaldo] Pin transactie fouten gevonden.', $body);
				$mail->send();
			}
		} elseif ($this->interactive) {
			if (count($pintransacties) > 0) {
				$output->writeln(sprintf("Er zijn %d pin transacties gedownload en gematcht.", count($pintransacties)));
			} else {
				$output->writeln("Er is niets gedownload!");
			}
		}
	}
}
