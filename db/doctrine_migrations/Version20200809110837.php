<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200809110837 extends AbstractMigration {
	public function getDescription(): string {
		return 'Voeg series en last_used toe aan login_remember.';
	}

	public function up(Schema $schema): void {
		$this->addSql(<<<'SQL'
ALTER TABLE login_remember
    ADD series VARCHAR(255) NOT NULL,
    ADD last_used DATETIME NOT NULL
SQL
		);
	}

	public function down(Schema $schema): void {
		$this->addSql(<<<'SQL'
ALTER TABLE login_remember
    DROP series,
    DROP last_used
SQL
		);
	}
}
