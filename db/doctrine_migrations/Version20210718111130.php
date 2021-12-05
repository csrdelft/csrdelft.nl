<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210718111130 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Maak csrPas nullable';
	}

	public function up(Schema $schema): void
	{
		$this->addSql('ALTER TABLE declaratie CHANGE csr_pas csr_pas TINYINT(1) DEFAULT NULL');
	}

	public function down(Schema $schema): void
	{
		$this->addSql('ALTER TABLE declaratie CHANGE csr_pas csr_pas TINYINT(1) NOT NULL');
	}
}
