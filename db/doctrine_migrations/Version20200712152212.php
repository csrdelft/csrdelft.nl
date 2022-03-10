<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200712152212 extends AbstractMigration {
	public function getDescription(): string {
		return 'Maak pass_since nullable in Account';
	}

	public function up(Schema $schema): void {
		$this->addSql('ALTER TABLE accounts CHANGE pass_since pass_since DATETIME DEFAULT NULL');
		//$this->addSql('UPDATE accounts SET pass_since = NULL WHERE pass_since = \'0000-00-00 00:00:00\';');
	}

	public function down(Schema $schema): void {
		$this->addSql('UPDATE accounts SET pass_since = \'0000-00-00 00:00:00\' WHERE pass_since IS NULL');
		$this->addSql('ALTER TABLE accounts CHANGE pass_since pass_since DATETIME NOT NULL');
	}
}
