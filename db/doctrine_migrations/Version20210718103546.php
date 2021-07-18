<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210718103546 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Voeg omschrijving veld toe aan declaratie';
	}

	public function up(Schema $schema): void
	{
		$this->addSql('ALTER TABLE declaratie ADD omschrijving VARCHAR(255) DEFAULT NULL');
	}

	public function down(Schema $schema): void
	{
		$this->addSql('ALTER TABLE declaratie DROP omschrijving');
	}
}
