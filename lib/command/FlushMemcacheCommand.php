<?php


namespace CsrDelft\command;


use Memcache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FlushMemcacheCommand extends Command {
	/**
	 * @var Memcache
	 */
	private $cache;

	public function __construct(Memcache $cache) {
		parent::__construct();
		$this->cache = $cache;
	}

	public function configure() {
		$this
			->setName('stek:cache:flush')
			->setDescription('Flush de memcache');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		if ($this->cache->flush()) {
			$output->writeln('Memcache succesvol geflushed');
		} else {
			$output->writeln('Memcache flushen mislukt');
			$output->writeln(error_get_last()["message"]);
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
