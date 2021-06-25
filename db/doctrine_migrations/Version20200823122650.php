<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200823122650 extends AbstractMigration {
	public function getDescription(): string {
		return 'Maak primary keys van Eetplan en EetplanBekenden simpel.';
	}

	public function up(Schema $schema): void {
		$this->addSql('ALTER TABLE eetplan ADD id INT AUTO_INCREMENT NOT NULL, CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' DEFAULT NULL, CHANGE woonoord_id woonoord_id INT DEFAULT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
		$this->addSql('CREATE UNIQUE INDEX noviet_woonoord ON eetplan (uid, woonoord_id)');
		$this->addSql('ALTER TABLE eetplan_bekenden ADD id INT AUTO_INCREMENT NOT NULL, CHANGE uid1 uid1 VARCHAR(4) COMMENT \'(DC2Type:uid)\' DEFAULT NULL, CHANGE uid2 uid2 VARCHAR(4) COMMENT \'(DC2Type:uid)\' DEFAULT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
		$this->addSql('CREATE UNIQUE INDEX noviet1_noviet2 ON eetplan_bekenden (uid1, uid2)');
	}

	public function down(Schema $schema): void {
		$this->addSql('ALTER TABLE eetplan MODIFY id INT NOT NULL');
		$this->addSql('DROP INDEX noviet_woonoord ON eetplan');
		$this->addSql('ALTER TABLE eetplan DROP PRIMARY KEY');
		$this->addSql('ALTER TABLE eetplan DROP id, CHANGE woonoord_id woonoord_id INT NOT NULL, CHANGE uid uid VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->addSql('ALTER TABLE eetplan ADD PRIMARY KEY (uid, woonoord_id)');
		$this->addSql('ALTER TABLE eetplan_bekenden MODIFY id INT NOT NULL');
		$this->addSql('DROP INDEX noviet1_noviet2 ON eetplan_bekenden');
		$this->addSql('ALTER TABLE eetplan_bekenden DROP PRIMARY KEY');
		$this->addSql('ALTER TABLE eetplan_bekenden DROP id, CHANGE uid1 uid1 VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL, CHANGE uid2 uid2 VARCHAR(4) COMMENT \'(DC2Type:uid)\' NOT NULL');
		$this->addSql('ALTER TABLE eetplan_bekenden ADD PRIMARY KEY (uid1, uid2)');
	}
}
