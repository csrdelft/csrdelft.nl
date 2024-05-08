<?php

namespace CsrDelft\command;

use DateTimeImmutable;
use CsrDelft\common\Mail;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\repository\fiscaat\CiviBestellingRepository;
use CsrDelft\repository\pin\PinTransactieMatchRepository;
use CsrDelft\repository\pin\PinTransactieRepository;
use CsrDelft\service\MailService;
use CsrDelft\service\pin\PinTransactieDownloader;
use CsrDelft\service\pin\PinTransactieMatcher;
use DateInterval;
use DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Twig\Environment;

class PinTransactiesDownloadenCommand extends Command
{
	protected static $defaultName = 'fiscaat:pintransacties:download';
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
	/**
	 * @var Environment
	 */
	private $twig;
	/**
	 * @var MailService
	 */
	private $mailService;

	public function __construct(
		Environment $twig,
		PinTransactieRepository $pinTransactieRepository,
		PinTransactieMatchRepository $pinTransactieMatchRepository,
		PinTransactieMatcher $pinTransactieMatcher,
		PinTransactieDownloader $pinTransactieDownloader,
		CiviBestellingRepository $civiBestellingRepository,
		MailService $mailService
	) {
		parent::__construct(null);
		$this->pinTransactieRepository = $pinTransactieRepository;
		$this->pinTransactieMatchRepository = $pinTransactieMatchRepository;
		$this->pinTransactieMatcher = $pinTransactieMatcher;
		$this->pinTransactieDownloader = $pinTransactieDownloader;
		$this->civiBestellingRepository = $civiBestellingRepository;
		$this->twig = $twig;
		$this->mailService = $mailService;
	}

	protected function configure()
	{
		$this->setDescription(
			'Download pintransacties van aangegeven periode en probeer te matchen met bestellingen.'
		)
			->addArgument(
				'vanaf',
				InputArgument::OPTIONAL,
				'Vanaf welke datum wil je downloaden (jjjj-mm-dd)'
			)
			->addArgument(
				'tot',
				InputArgument::OPTIONAL,
				'T/m welke datum wil je downloaden (jjjj-mm-dd)'
			)
			->addOption(
				'disableSSL',
				null,
				InputOption::VALUE_NONE,
				'Zet SSL validatie bij ophalen pintransacties uit - handmatig gebruik i.v.m. problemen Payplaza'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->interactive =
			$input->isInteractive() && !$input->getOption('no-interaction');

		if ($this->interactive) {
			$vanaf = DateTimeImmutable::createFromFormat(
				'Y-m-d',
				$input->getArgument('vanaf')
			);
			if (!$vanaf) {
				$output->writeln('Geef een geldige vanaf datum (jjjj-mm-dd)');
				return 1;
			}
			$tot = $input->getArgument('tot')
				? DateTime::createFromFormat('Y-m-d', $input->getArgument('tot'))
				: clone $vanaf;
			if (!$tot) {
				$output->writeln('Geef een geldige tot datum (jjjj-mm-dd)');
				return 1;
			}

			if ($vanaf > $tot) {
				$output->writeln('Tot datum ligt voor vanaf datum');
				return 1;
			}

			if ($input->getOption('disableSSL') === true) {
				$output->writeln('SSL is uitgeschakeld!');
				$this->pinTransactieDownloader->disableSSL = true;
			}

			$helper = $this->getHelper('question');
			$question = new ConfirmationQuestion(
				'Weet je zeker dat je pin transacties wil downloaden? [Y/n]',
				true
			);
			if (!$helper->ask($input, $output, $question)) {
				return 1;
			}

			for (
				$cur = $vanaf;
				$cur <= $tot;
				$cur = $cur->add(new DateInterval('P1D'))
			) {
				$date = $cur->format('Y-m-d');
				$output->writeln('<info>' . $date . '</info>');
				$from =
					DateUtil::dateFormatIntl($cur, DateUtil::DATE_FORMAT) . ' 12:00:00';
				$to =
					DateUtil::dateFormatIntl(
						$cur->add(new DateInterval('P1D')),
						DateUtil::DATE_FORMAT
					) . ' 12:00:00';
				$this->downloadDag($output, $from, $to);
			}
		} else {
			$moment = date_create_immutable()->sub(new DateInterval('P1D'));
			$from =
				DateUtil::dateFormatIntl(
					$moment->sub(new DateInterval('P1D')),
					DateUtil::DATE_FORMAT
				) . ' 12:00:00';
			$to =
				DateUtil::dateFormatIntl($moment, DateUtil::DATE_FORMAT) . ' 12:00:00';
			$output->writeln("Downloaden van $from tot $to");
			$this->downloadDag($output, $from, $to);
		}

		return 0;
	}

	private function downloadDag(OutputInterface $output, $from, $to)
	{
		// Verwijder eerdere download.
		$vorigePinTransacties = $this->pinTransactieRepository->getPinTransactieInMoment(
			$from,
			$to
		);

		$this->pinTransactieMatchRepository->cleanByTransactieIds(
			$vorigePinTransacties
		);
		$this->pinTransactieRepository->clean($vorigePinTransacties);

		// Download pintransacties en sla op in DB.
		$pintransacties = $this->pinTransactieDownloader->download(
			$from,
			$_ENV['PIN_URL'],
			$_ENV['PIN_CLIENT_ID'],
			$_ENV['PIN_CERTIFICATE_PATH'],
			$_ENV['PIN_PRIVATE_KEY_PATH']
		);

		// Haal pinbestellingen op.
		$pinbestellingen = $this->civiBestellingRepository->getPinBestellingInMoment(
			$from,
			$to
		);

		$this->pinTransactieMatcher->setPinTransacties($pintransacties);
		$this->pinTransactieMatcher->setPinBestellingen($pinbestellingen);

		$this->pinTransactieMatcher->clean();
		$this->pinTransactieMatcher->match();
		$this->pinTransactieMatcher->save();

		if ($this->pinTransactieMatcher->bevatFouten()) {
			$report = $this->pinTransactieMatcher->genereerReport();

			$body = $this->twig->render('mail/bericht/pintransactie.mail.twig', [
				'from' => $from,
				'to' => $to,
				'report' => $report,
			]);

			if ($this->interactive) {
				$output->writeln($report);
				$output->writeln(
					'De mail is niet verzonden, want de sessie is in interactieve modus.'
				);
				$output->writeln(
					sprintf(
						'Er zijn %d pin transacties gedownload.',
						count($pintransacties)
					)
				);
			} else {
				$mail = new Mail(
					[$_ENV['PIN_MONITORING_EMAIL'] => 'Pin Transactie Monitoring'],
					'[CiviSaldo] Pin transactie fouten gevonden.',
					$body
				);
				$this->mailService->send($mail);
			}
		} elseif ($this->interactive) {
			if (count($pintransacties) > 0) {
				$output->writeln(
					sprintf(
						'Er zijn %d pin transacties gedownload en gematcht.',
						count($pintransacties)
					)
				);
			} else {
				$output->writeln('Er is niets gedownload!');
			}
		}
	}
}
