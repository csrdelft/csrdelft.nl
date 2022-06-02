<?php

namespace CsrDelft\command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronMonthlyCommand extends Command
{
	protected static $defaultName = 'stek:cron:monthly';

	protected function configure()
	{
		$this
			->setDescription('Voer maandelijkse acties uit');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$start = microtime(true);

		$output->writeln(getDateTime() . " stek:cron:monthly");

		$this->getApplication()
			->find(SponsorAffiliateDownloadCommand::getDefaultName())
			->run(new ArrayInput([]), $output);

		$finish = microtime(true) - $start;

		$output->writeln(getDateTime() . ' Finished in ' . (int)$finish . " seconds.");

		return Command::SUCCESS;
	}
}
