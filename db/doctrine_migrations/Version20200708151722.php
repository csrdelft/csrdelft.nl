<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200708151722 extends AbstractMigration {
	public function getDescription(): string {
		return 'Maak parent_id in MenuItem nullabe, dit versimpelt veel logica';
	}

	public function up(Schema $schema): void {
		$this->addSql('ALTER TABLE menus CHANGE parent_id parent_id INT DEFAULT NULL');
		$this->addSql('UPDATE menus SET parent_id = NULL WHERE parent_id = 0');
		$this->addSql('DELETE FROM menus WHERE item_id = 0');
	}

	public function down(Schema $schema): void {
		// Allow inserting a 0
		$this->addSql('SET @@SQL_MODE = CONCAT(@@SQL_MODE, \',NO_AUTO_VALUE_ON_ZERO\');');
		$this->addSql('INSERT INTO menus (item_id, parent_id, volgorde, tekst, link, rechten_bekijken, zichtbaar) VALUES (0, 0, 0, \'\', \'\', null, 0)');
		$this->addSql('UPDATE menus SET parent_id = 0 WHERE parent_id IS NULL');
		$this->addSql('ALTER TABLE menus CHANGE parent_id parent_id INT NOT NULL');
	}
}
