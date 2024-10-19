<?php

namespace CsrDelft\command;

use ErrorException;
use CsrDelft\common\Util\FileUtil;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsCommand(name: 'stek:cache:flush', description: 'Flush memcached')]
class FlushMemcacheCommand extends Command
{
	public function __construct(
		private readonly CacheInterface $appCache,
		private readonly CacheInterface $systemCache
	) {
		parent::__construct();
	}

	protected function execute(
		InputInterface $input,
		OutputInterface $output
	): int {
		if ($this->appCache == null) {
			$output->writeln('Geen cache geinstalleerd');
			return 1;
		} elseif ($this->appCache->clear()) {
			$output->writeln('cache.app succesvol geflushed');
		} else {
			$output->writeln('cache.app flushen mislukt');
			$output->writeln(error_get_last()['message']);
		}

		if ($this->systemCache == null) {
			$output->writeln('Geen cache geinstalleerd');
			return 1;
		} elseif ($this->systemCache->clear()) {
			$output->writeln('cache.system succesvol geflushed');
		} else {
			$output->writeln('cache.system flushen mislukt');
			$output->writeln(error_get_last()['message']);
		}

		try {
			FileUtil::delTree(CONFIG_CACHE_PATH);

			$output->writeln('Instelling cache succesvol verwijderd');
		} catch (ErrorException) {
			$output->writeln('Instelling cache verwijderen mislukt');
			$output->writeln(error_get_last()['message']);
		}

		return 0;
	}
}
