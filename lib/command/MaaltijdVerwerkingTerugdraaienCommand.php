<?php

namespace CsrDelft\command;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\fiscaat\CiviBestelling;
use CsrDelft\model\fiscaat\CiviBestellingModel;
use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\Orm\Persistence\Database;
use Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class MaaltijdVerwerkingTerugdraaienCommand extends ContainerAwareCommand {
	/** @var MaaltijdenModel */
	private $maaltijdenModel;
	/** @var CiviBestellingModel */
	private $civiBestellingModel;

	public function __construct(MaaltijdenModel $maaltijdenModel, CiviBestellingModel $civiBestellingModel) {
		$this->maaltijdenModel = $maaltijdenModel;
		$this->civiBestellingModel = $civiBestellingModel;

		parent::__construct();
	}

	protected function configure() {
		$this
			->setName('maalcie:fiscaat:terugdraaien')
			->setDescription('Draai verwerking van maaltijden op bepaalde datum terug. Let op: hiermee wordt ook civi-saldo teruggestort naar oud-leden.');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$helper = $this->getHelper('question');

		// Bepaal datum
		$datum = null;
		do {
			$question = new Question("Van welke maaltijd wil je de verwerking terugdraaien? ");
			$mid = $helper->ask($input, $output, $question);
			if (is_numeric($mid)) {
				try {
					$maaltijd = $this->maaltijdenModel->getMaaltijd($mid);
						if (!$maaltijd->verwerkt) {
						$output->writeln("Maaltijd is nog niet verwerkt");
					} else {
						$datum = $maaltijd->datum;
					}
				} catch (CsrGebruikerException $exception) {
					$output->writeln($exception->getMessage());
				}
			}
		} while ($datum != null);

		$output->writeln("");

		// Haal maaltijden op deze datum op
		$maaltijden = $this->maaltijdenModel->find('datum = ? AND verwerkt = 1', [$datum])->fetchAll();
		$maaltijdTekst = count($maaltijden) > 1 ? count($maaltijden) . ' maaltijden' : 'maaltijd';
		$output->writeln("De verwerking van de volgende {$maaltijdTekst} wordt hiermee ongedaan gemaakt:");
		foreach ($maaltijden as $maaltijd) {
			$output->writeln("- " . $maaltijd->titel . " " . $maaltijd->datum);
		}

		// Haal bestellingen op
		$comment = sprintf('Datum maaltijd: %s', date('Y-M-d', strtotime($datum)));
		$bestellingen = $this->civiBestellingModel->find('cie = "maalcie" AND comment = ? AND deleted = 0', [$comment])->fetchAll();
		$leden = [];
		$som = 0;
		foreach ($bestellingen as $bestelling) {
			/** @var $bestelling CiviBestelling */
			if (!in_array($bestelling->uid, $leden)) {
				$leden[] = $bestelling->uid;
			}
			$som += $bestelling->totaal;
		}

		$output->writeln("");
		$aantal = count($bestellingen);
		$output->writeln(count($leden) . " leden krijgen totaal " . format_bedrag($som) . " teruggestort op hun CiviSaldo doordat {$aantal} bestellingen teruggedraaid worden.");
		$output->writeln("");

		// Bevestiging
		$confirm = new ConfirmationQuestion("Wil je de verwerking van deze {$maaltijdTekst} nu terugdraaien? [j/n] ", false, '/^j/i');
		$confirmed = $helper->ask($input, $output, $confirm);
		if (!$confirmed) {
			$output->writeln("Geannuleerd.");
			return;
		};

		$output->writeln("");

		// Terugdraaien
		$progress = new ProgressBar($output, count($bestellingen));
		try {
			Database::transaction(function () use ($bestellingen, $progress, $maaltijden) {
				reset($bestellingen);
				foreach ($bestellingen as $bestelling) {
					$this->civiBestellingModel->revert($bestelling);
					$progress->advance();
				}

				reset($maaltijden);
				foreach ($maaltijden as $maaltijd) {
					$maaltijd->verwerkt = false;
					$this->maaltijdenModel->update($maaltijd);
				}
			});
		} catch (Exception $e) {
			$progress->clear();
			$output->writeln("Terugdraaien mislukt:");
			$output->writeln($e->getMessage());
			$output->writeln($e->getTraceAsString());
			return;
		}

		$progress->finish();
		$output->writeln("");
		$output->writeln("Succesvol teruggedraaid!");
	}
}
