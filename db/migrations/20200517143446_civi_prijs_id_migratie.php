<?php

use Phinx\Migration\AbstractMigration;

class CiviPrijsIdMigratie extends AbstractMigration {
	public function down() {
		$this->query('ALTER TABLE `CiviPrijs`
	DROP COLUMN `id`,
	DROP PRIMARY KEY,
	DROP INDEX `unique_van_product_id`,
	ADD PRIMARY KEY (`van`, `product_id`);');
	}

	public function up() {
		$this->query('ALTER TABLE `CiviPrijs`
	ADD COLUMN `id` INT NOT NULL AUTO_INCREMENT AFTER `prijs`,
	DROP PRIMARY KEY,
	ADD PRIMARY KEY (`id`),
	ADD UNIQUE INDEX `unique_van_product_id` (`van`, `product_id`);');
	}
}

