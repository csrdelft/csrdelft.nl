<?php

use Phinx\Migration\AbstractMigration;

class CiviSaldoPkMigratie extends AbstractMigration {
	public function up() {
		$this->query('ALTER TABLE `CiviBestelling` DROP FOREIGN KEY `CiviBestelling_ibfk_1`;');
		$this->query('ALTER TABLE CiviSaldo MODIFY id INT NOT NULL');
		$this->query('DROP INDEX uid ON CiviSaldo');
		$this->query('ALTER TABLE CiviSaldo DROP PRIMARY KEY');
		$this->query('ALTER TABLE CiviSaldo DROP id');
		$this->query('ALTER TABLE CiviSaldo ADD PRIMARY KEY (uid)');
		$this->query('ALTER TABLE `CiviBestelling` ADD CONSTRAINT `CiviBestelling_ibfk_1` FOREIGN KEY (uid) REFERENCES CiviSaldo (uid)');
	}

	public function down() {
		$this->query('ALTER TABLE `CiviBestelling` DROP FOREIGN KEY `CiviBestelling_ibfk_1`;');
		$this->query('ALTER TABLE CiviSaldo ADD id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
		$this->query('CREATE UNIQUE INDEX uid ON CiviSaldo (uid)');
		$this->query('ALTER TABLE `CiviBestelling` ADD CONSTRAINT `CiviBestelling_ibfk_1` FOREIGN KEY (uid) REFERENCES CiviSaldo (uid)');
	}
}
