<?php


namespace CsrDelft\command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Cache\CacheInterface;

class FlushMemcacheCommand extends Command
{
	/**
	 * @var CacheInterface
	 */
	private $cache;

	public function __construct(CacheInterface $cache)
	{
		parent::__construct();
		$this->cache = $cache;
	}

	public function configure()
	{
		$this
			->setName('stek:cache:flush')
			->setDescription('Flush de memcache');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if ($this->cache == null) {
			$output->writeln('Geen cache geinstalleerd');
			return 1;
		} else {
			if ($this->cache->clear()) {
				$output->writeln('Memcache succesvol geflushed');
			} else {
				$output->writeln('Memcache flushen mislukt');
				$output->writeln(error_get_last()["message"]);
			}
		}

		try {
			delTree(CONFIG_CACHE_PATH);

			$output->writeln('Instelling cache succesvol verwijderd');
		} catch (\ErrorException $exception) {
			$output->writeln('Instelling cache verwijderen mislukt');
			$output->writeln(error_get_last()["message"]);
		}

		return 0;
	}
}
