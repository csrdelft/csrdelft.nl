<?php

namespace CsrDelft\command;

use CsrDelft\model\entity\fiscaat\CiviBestelling;
use CsrDelft\model\entity\fiscaat\CiviBestellingInhoud;
use CsrDelft\model\fiscaat\CiviBestellingModel;
use CsrDelft\model\fiscaat\CiviProductModel;
use CsrDelft\model\fiscaat\CiviSaldoModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\repository\StekPakketRepository;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StekPakketVanCiviSaldo extends Command {
	/** @var CiviBestellingModel */
	private $civiBestellingModel;
	/** @var StekPakketRepository */
	private $stekPakketRepository;
	/** @var CiviProductModel */
	private $civiProductModel;

	public function __construct(CiviBestellingModel $civiBestellingModel, StekPakketRepository $stekPakketRepository, CiviProductModel $civiProductModel) {
		$this->civiBestellingModel = $civiBestellingModel;
		$this->stekPakketRepository = $stekPakketRepository;
		$this->civiProductModel = $civiProductModel;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setName('pubcie:stekpakketten')
			->setDescription('Verwerkt stekpakketen in CiviSaldo van leden');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		// Haal stekpakketten op
		$stekpakketten = $this->stekPakketRepository->findBy(['donatie' => true]);

		// Verwerken
		$progress = new ProgressBar($output, count($stekpakketten));
		try {
			Database::transaction(function () use ($stekpakketten, $progress) {
				foreach ($stekpakketten as $stekPakket) {
					if ($stekPakket->prijs <= 0) {
						$progress->setMaxSteps($progress->getMaxSteps() - 1);
						continue;
					}

					$bestelling = new CiviBestelling();
					$bestelling->cie = 'anders';
					$bestelling->uid = $stekPakket->uid;
					$bestelling->deleted = false;
					$bestelling->moment = getDateTime();

					$inhoud = new CiviBestellingInhoud();
					$inhoud->aantal = $stekPakket->prijs * 100;
					$inhoud->product_id = 150;

					$bestelling->inhoud[] = $inhoud;
					$bestelling->totaal = $this->civiProductModel->getProduct($inhoud->product_id)->prijs * $stekPakket->prijs * 100;

					$this->civiBestellingModel->create($bestelling);
					CiviSaldoModel::instance()->verlagen($stekPakket->uid, $bestelling->totaal);
					$progress->advance();
				}
			});
		} catch (Exception $e) {
			$progress->clear();
			$output->writeln("Verwerken mislukt mislukt:");
			$output->writeln($e->getMessage());
			$output->writeln($e->getTraceAsString());
			return 1;
		}

		$progress->finish();
		$output->writeln("");
		$output->writeln("Succesvol verwerkt!");
		return 0;
	}
}
