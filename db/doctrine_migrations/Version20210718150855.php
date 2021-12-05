<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210718150855 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Voeg datums toe aan declaratie';
	}

	public function up(Schema $schema): void
	{
		$this->addSql('ALTER TABLE declaratie ADD uitbetaald DATETIME DEFAULT NULL, CHANGE ingediend ingediend DATETIME DEFAULT NULL, CHANGE beoordeeld beoordeeld DATETIME DEFAULT NULL');
	}

	public function down(Schema $schema): void
	{
		$this->addSql('ALTER TABLE declaratie DROP uitbetaald, CHANGE ingediend ingediend TINYINT(1) NOT NULL, CHANGE beoordeeld beoordeeld TINYINT(1) NOT NULL');
	}
}
