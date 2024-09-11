<?php

namespace CsrDelft\command;

use CsrDelft\common\Util\DateUtil;
use CsrDelft\repository\DebugLogRepository;
use CsrDelft\repository\instellingen\InstellingenRepository;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\repository\security\OneTimeTokensRepository;
use CsrDelft\service\corvee\CorveeHerinneringService;
use CsrDelft\service\forum\ForumService;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronCommand extends Command
{
	protected static $defaultName = 'stek:cron';

	protected function configure()
	{
		$this->setDescription('Voer alle periodieke taken uit');
	}

	public function __construct(
		private readonly DebugLogRepository $debugLogRepository,
		private readonly OneTimeTokensRepository $oneTimeTokensRepository,
		private readonly InstellingenRepository $instellingenRepository,
		private readonly LidInstellingenRepository $lidInstellingenRepository,
		private readonly CorveeHerinneringService $corveeHerinneringService,
		private readonly ForumService $forumService
	) {
		parent::__construct(null);
	}

	protected function execute(
		InputInterface $input,
		OutputInterface $output
	): int {
		$start = microtime(true);

		$output->writeln('debuglog opschonen', OutputInterface::VERBOSITY_VERBOSE);
		try {
			$this->debugLogRepository->opschonen();
		} catch (Exception $e) {
			$output->writeln($e->getMessage());
			$this->debugLogRepository->log(
				'cron.php',
				'debugLogRepository->opschonen',
				[],
				$e
			);
		}

		$output->writeln(
			'One time tokens opschonen',
			OutputInterface::VERBOSITY_VERBOSE
		);
		try {
			$this->oneTimeTokensRepository->opschonen();
		} catch (Exception $e) {
			$output->writeln($e->getMessage());
			$this->debugLogRepository->log(
				'cron.php',
				'oneTimeTokensRepository->opschonen',
				[],
				$e
			);
		}

		$output->writeln(
			'Instellingen opschonen',
			OutputInterface::VERBOSITY_VERBOSE
		);
		try {
			$this->instellingenRepository->opschonen();
			$this->lidInstellingenRepository->opschonen();
		} catch (Exception $e) {
			$output->writeln($e->getMessage());
			$this->debugLogRepository->log(
				'cron.php',
				'(Lid)InstellingenRepository->opschonen',
				[],
				$e
			);
		}

		$output->writeln(
			'Corvee herinneringen',
			OutputInterface::VERBOSITY_VERBOSE
		);
		try {
			$this->corveeHerinneringService->stuurHerinneringen();
		} catch (Exception $e) {
			$output->writeln($e->getMessage());
			$this->debugLogRepository->log(
				'cron.php',
				'corveeHerinneringenService->stuurHerinneringen',
				[],
				$e
			);
		}

		$output->writeln('Forum opschonen', OutputInterface::VERBOSITY_VERBOSE);
		try {
			$this->forumService->opschonen();
		} catch (Exception $e) {
			$output->writeln($e->getMessage());
			$this->debugLogRepository->log(
				'cron.php',
				'forumCategorieRepository->opschonen',
				[],
				$e
			);
		}

		$ret = $this->getApplication()
			->find(PinTransactiesDownloadenCommand::getDefaultName())
			->run(new ArrayInput(['--no-interaction' => true]), $output);

		if ($ret !== 0) {
			$output->writeln($ret);
			$this->debugLogRepository->log(
				'cron.php',
				'pin_transactie_download',
				[],
				'exit ' . $ret
			);
		}

		// Verwijder verlopen oauth2 tokens
		$this->getApplication()
			->find('league:oauth2-server:clear-expired-tokens')
			->run(new ArrayInput([]), $output);

		$finish = microtime(true) - $start;
		$output->writeln(
			DateUtil::getDateTime() . ' Finished in ' . (int) $finish . ' seconds',
			OutputInterface::VERBOSITY_VERBOSE
		);

		return Command::SUCCESS;
	}
}
