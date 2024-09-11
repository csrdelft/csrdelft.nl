<?php

namespace CsrDelft\command;

use CsrDelft\common\Util\FileUtil;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Cache\CacheInterface;

class FlushMemcacheCommand extends Command
{
	public function __construct(
		private readonly CacheInterface $appCache,
		private readonly CacheInterface $systemCache
	) {
		parent::__construct();
	}

	public function configure()
	{
		$this->setName('stek:cache:flush')->setDescription('Flush de memcache');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if ($this->appCache == null) {
			$output->writeln('Geen cache geinstalleerd');
			return 1;
		} else {
			if ($this->appCache->clear()) {
				$output->writeln('cache.app succesvol geflushed');
			} else {
				$output->writeln('cache.app flushen mislukt');
				$output->writeln(error_get_last()['message']);
			}
		}

		if ($this->systemCache == null) {
			$output->writeln('Geen cache geinstalleerd');
			return 1;
		} else {
			if ($this->systemCache->clear()) {
				$output->writeln('cache.system succesvol geflushed');
			} else {
				$output->writeln('cache.system flushen mislukt');
				$output->writeln(error_get_last()['message']);
			}
		}

		try {
			FileUtil::delTree(CONFIG_CACHE_PATH);

			$output->writeln('Instelling cache succesvol verwijderd');
		} catch (\ErrorException) {
			$output->writeln('Instelling cache verwijderen mislukt');
			$output->writeln(error_get_last()['message']);
		}

		return 0;
	}
}
