<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210718122303 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Sta null toe in declaratie regel';
	}

	public function up(Schema $schema): void
	{
		$this->addSql('ALTER TABLE declaratie_regel CHANGE bedrag bedrag DOUBLE PRECISION DEFAULT NULL, CHANGE incl_btw incl_btw TINYINT(1) DEFAULT NULL, CHANGE btw btw INT DEFAULT NULL, CHANGE omschrijving omschrijving VARCHAR(255) DEFAULT NULL');
	}

	public function down(Schema $schema): void
	{
		$this->addSql('ALTER TABLE declaratie_regel CHANGE bedrag bedrag DOUBLE PRECISION NOT NULL, CHANGE incl_btw incl_btw TINYINT(1) NOT NULL, CHANGE btw btw INT NOT NULL, CHANGE omschrijving omschrijving VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`');
	}
}
