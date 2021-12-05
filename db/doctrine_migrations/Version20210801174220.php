<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210801174220 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Voeg email en prefix toe aan wachtrij';
	}

	public function up(Schema $schema): void
	{
		$this->addSql('ALTER TABLE declaratie_wachtrij ADD email VARCHAR(255) DEFAULT NULL, ADD prefix VARCHAR(2) DEFAULT NULL');
	}

	public function down(Schema $schema): void
	{
		$this->addSql('ALTER TABLE declaratie_wachtrij DROP email, DROP prefix');
	}
}
