<?php

namespace CsrDelft\command;

use CsrDelft\common\Util\DateUtil;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[
	AsCommand(
		name: 'stek:cron:monthly',
		description: 'Voer maandelijkse acties uit'
	)
]
class CronMonthlyCommand extends Command
{
	protected function execute(
		InputInterface $input,
		OutputInterface $output
	): int {
		$start = microtime(true);

		$output->writeln(DateUtil::getDateTime() . ' stek:cron:monthly');

		$this->getApplication()
			->find(SponsorAffiliateDownloadCommand::getDefaultName())
			->run(new ArrayInput([]), $output);

		$finish = microtime(true) - $start;

		$output->writeln(
			DateUtil::getDateTime() . ' Finished in ' . (int) $finish . ' seconds.'
		);

		return Command::SUCCESS;
	}
}
