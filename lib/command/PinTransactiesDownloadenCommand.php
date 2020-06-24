<?php

namespace CsrDelft\command;

use DateInterval;
use DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PinTransactiesDownloadenCommand extends Command {
	protected function configure() {
		$this
			->setName('fiscaat:pintransacties:download')
			->setDescription('Download pintransacties van aangegeven periode en probeer te matchen met bestellingen.')
			->addArgument('vanaf', InputArgument::REQUIRED, 'Vanaf welke datum wil je downloaden (jjjj-mm-dd)')
			->addArgument('tot', InputArgument::OPTIONAL, 'T/m welke datum wil je downloaden (jjjj-mm-dd)');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$vanaf = DateTime::createFromFormat('Y-m-d', $input->getArgument('vanaf'));
		if (!$vanaf) {
			$output->writeln("Geef een geldige vanaf datum (jjjj-mm-dd)");
			return 1;
		}
		$tot = $input->getArgument('tot') ? DateTime::createFromFormat('Y-m-d', $input->getArgument('tot')) : $vanaf;
		if (!$tot) {
			$output->writeln("Geef een geldige tot datum (jjjj-mm-dd)");
			return 1;
		}

		if ($vanaf > $tot) {
			$output->writeln("Tot datum ligt voor vanaf datum");
			return 1;
		}

		/** @var DateTime $cur */
		for ($cur = $vanaf; $cur <= $tot; $cur->add(new DateInterval('P1D'))) {
			$date = $cur->format("Y-m-d");
			$output->writeln("<info>" . $date . "</info>");
			passthru('php bin/cron/pin_transactie_download.php ' . $date, $ret);
		}

		return 0;
	}
}
