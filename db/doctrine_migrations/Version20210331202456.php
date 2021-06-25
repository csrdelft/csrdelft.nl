<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210331202456 extends AbstractMigration
{
	public function getDescription(): string
	{
		return 'Voeg uuid toe aan account';
	}

	public function up(Schema $schema): void
	{
		$this->addSql('ALTER TABLE accounts ADD uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
		// genereer nieuwe uuids
		$this->addSql('UPDATE accounts SET uuid = (SELECT (UNHEX(REPLACE(UUID(), \'-\', \'\'))))');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_CAC89EACD17F50A6 ON accounts (uuid)');
	}

	public function down(Schema $schema): void
	{
		$this->addSql('DROP INDEX UNIQ_CAC89EACD17F50A6 ON accounts');
		$this->addSql('ALTER TABLE accounts DROP uuid');
	}
}
