<?php

namespace CsrDelft\command;

use CsrDelft\repository\DebugLogRepository;
use CsrDelft\repository\forum\ForumCategorieRepository;
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
use Trikoder\Bundle\OAuth2Bundle\Command\ClearExpiredTokensCommand;
use Trikoder\Bundle\OAuth2Bundle\Command\ClearRevokedTokensCommand;

class CronCommand extends Command
{
	protected static $defaultName = 'stek:cron';
	/**
	 * @var DebugLogRepository
	 */
	private $debugLogRepository;
	/**
	 * @var OneTimeTokensRepository
	 */
	private $oneTimeTokensRepository;
	/**
	 * @var InstellingenRepository
	 */
	private $instellingenRepository;
	/**
	 * @var LidInstellingenRepository
	 */
	private $lidInstellingenRepository;
	/**
	 * @var CorveeHerinneringService
	 */
	private $corveeHerinneringService;
	/**
	 * @var ForumService
	 */
	private $forumService;

	protected function configure()
	{
		$this->setDescription('Voer alle periodieke taken uit');
	}

	public function __construct(
		DebugLogRepository $debugLogRepository,
		OneTimeTokensRepository $oneTimeTokensRepository,
		InstellingenRepository $instellingenRepository,
		LidInstellingenRepository $lidInstellingenRepository,
		CorveeHerinneringService $corveeHerinneringService,
		ForumService $forumService
	) {
		parent::__construct(null);
		$this->debugLogRepository = $debugLogRepository;
		$this->oneTimeTokensRepository = $oneTimeTokensRepository;
		$this->instellingenRepository = $instellingenRepository;
		$this->lidInstellingenRepository = $lidInstellingenRepository;
		$this->corveeHerinneringService = $corveeHerinneringService;
		$this->forumService = $forumService;
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
			->find(ClearExpiredTokensCommand::getDefaultName())
			->run(new ArrayInput([]), $output);

		// Verwijder revoked oauth2 tokens
		$this->getApplication()
			->find(ClearRevokedTokensCommand::getDefaultName())
			->run(new ArrayInput([]), $output);

		$finish = microtime(true) - $start;
		$output->writeln(
			getDateTime() . ' Finished in ' . (int) $finish . ' seconds',
			OutputInterface::VERBOSITY_VERBOSE
		);

		return Command::SUCCESS;
	}
}
