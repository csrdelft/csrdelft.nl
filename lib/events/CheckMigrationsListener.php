<?php

namespace CsrDelft\events;

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\AvailableMigration;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Log\Logger;

class CheckMigrationsListener
{
	public function __construct(
		private readonly DependencyFactory $dependencyFactory,
		private readonly LoggerInterface $logger
	) {
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
				array_map(
					fn(
						AvailableMigration $availableMigration
					) => $availableMigration->getVersion() .
						': ' .
						$availableMigration->getMigration()->getDescription(),
					$migrationStatusCalculator->getNewMigrations()->getItems()
				)
			);
		}
	}
}
