<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200810205331 extends AbstractMigration {
	public function getDescription(): string {
		return 'Drop de phinxlog tabel als deze bestaat (dit is alleen het geval als er ooit phinx gebruikt is)';
	}

	public function up(Schema $schema): void {
		$this->addSql(<<<SQL
DROP TABLE IF EXISTS phinxlog;
SQL
		);
	}

	public function down(Schema $schema): void {
		// nothing
	}
}
