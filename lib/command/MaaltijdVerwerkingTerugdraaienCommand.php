<?php

namespace CsrDelft\command;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Util\BedragUtil;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\entity\fiscaat\CiviBestelling;
use CsrDelft\repository\fiscaat\CiviBestellingRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class MaaltijdVerwerkingTerugdraaienCommand extends Command
{
	public function __construct(
		private readonly MaaltijdenRepository $maaltijdenRepository,
		private readonly CiviBestellingRepository $civiBestellingRepository,
		private readonly EntityManagerInterface $em
	) {
		parent::__construct();
	}

	protected function configure()
	{
		$this->setName('maalcie:fiscaat:terugdraaien')->setDescription(
			'Draai verwerking van maaltijden op bepaalde datum terug. Let op: hiermee wordt ook civi-saldo teruggestort naar oud-leden.'
		);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$helper = $this->getHelper('question');

		// Bepaal datum
		$datum = null;
		do {
			$question = new Question(
				'Van welke maaltijd wil je de verwerking terugdraaien? '
			);
			$mid = $helper->ask($input, $output, $question);
			if (is_numeric($mid)) {
				try {
					$maaltijd = $this->maaltijdenRepository->getMaaltijd($mid);
					if (!$maaltijd->verwerkt) {
						$output->writeln('Maaltijd is nog niet verwerkt');
					} else {
						$datum = $maaltijd->datum;
					}
				} catch (CsrGebruikerException $exception) {
					$output->writeln($exception->getMessage());
				}
			}
		} while ($datum === null);

		$output->writeln('');

		// Haal maaltijden op deze datum op
		$maaltijden = $this->maaltijdenRepository->findBy([
			'datum' => $datum,
			'verwerkt' => '1',
		]);
		$maaltijdTekst =
			count($maaltijden) > 1 ? count($maaltijden) . ' maaltijden' : 'maaltijd';
		$output->writeln(
			"De verwerking van de volgende {$maaltijdTekst} wordt hiermee ongedaan gemaakt:"
		);
		foreach ($maaltijden as $maaltijd) {
			$output->writeln(
				'- ' .
					$maaltijd->titel .
					' ' .
					DateUtil::dateFormatIntl($maaltijd->datum, DateUtil::DATE_FORMAT)
			);
		}

		// Haal bestellingen op
		$comment = sprintf('Datum maaltijd: %s', $datum->format('Y-M-d'));
		$bestellingen = $this->civiBestellingRepository->findBy([
			'cie' => 'maalcie',
			'comment' => $comment,
			'deleted' => false,
		]);
		$leden = [];
		$som = 0;
		foreach ($bestellingen as $bestelling) {
			/** @var $bestelling CiviBestelling */
			if (!in_array($bestelling->uid, $leden)) {
				$leden[] = $bestelling->uid;
			}
			$som += $bestelling->totaal;
		}

		$output->writeln('');
		$aantal = count($bestellingen);
		$output->writeln(
			count($leden) .
				' leden krijgen totaal ' .
				BedragUtil::format_bedrag($som) .
				" teruggestort op hun CiviSaldo doordat {$aantal} bestellingen teruggedraaid worden."
		);
		$output->writeln('');

		// Bevestiging
		$confirm = new ConfirmationQuestion(
			"Wil je de verwerking van deze {$maaltijdTekst} nu terugdraaien? [j/n] ",
			false,
			'/^j/i'
		);
		$confirmed = $helper->ask($input, $output, $confirm);
		if (!$confirmed) {
			$output->writeln('Geannuleerd.');
			return 0;
		}

		$output->writeln('');

		// Terugdraaien
		$progress = new ProgressBar($output, count($bestellingen));
		try {
			$this->em->transactional(function () use (
				$bestellingen,
				$progress,
				$maaltijden
			): void {
				reset($bestellingen);
				foreach ($bestellingen as $bestelling) {
					$this->civiBestellingRepository->revert($bestelling);
					$progress->advance();
				}

				reset($maaltijden);
				foreach ($maaltijden as $maaltijd) {
					$maaltijd->verwerkt = false;
					$this->maaltijdenRepository->update($maaltijd);
				}
			});
		} catch (Exception $e) {
			$progress->clear();
			$output->writeln('Terugdraaien mislukt:');
			$output->writeln($e->getMessage());
			$output->writeln($e->getTraceAsString());
			return 1;
		}

		$progress->finish();
		$output->writeln('');
		$output->writeln('Succesvol teruggedraaid!');

		return 0;
	}
}
