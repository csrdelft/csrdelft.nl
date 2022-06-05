<?php

namespace CsrDelft\events;

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\AvailableMigration;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Log\Logger;

class CheckMigrationsListener
{
	/**
	 * @var DependencyFactory
	 */
	private $dependencyFactory;
	/**
	 * @var Logger
	 */
	private $logger;

	public function __construct(
		DependencyFactory $dependencyFactory,
		LoggerInterface $logger
	) {
		$this->dependencyFactory = $dependencyFactory;
		$this->logger = $logger;
	}

	public function onKernelRequest()
	{
		$migrationStatusCalculator = $this->dependencyFactory->getMigrationStatusCalculator();

		$aantalNieuweMigraties = count(
			$migrationStatusCalculator->getNewMigrations()
		);
		if ($aantalNieuweMigraties > 0) {
			$this->logger->alert(
				"Er zijn '{$aantalNieuweMigraties}' migraties die nog uitgevoerd moeten worden.",
				array_map(function (AvailableMigration $availableMigration) {
					return $availableMigration->getVersion() .
						': ' .
						$availableMigration->getMigration()->getDescription();
				}, $migrationStatusCalculator->getNewMigrations()->getItems())
			);
		}
	}
}
