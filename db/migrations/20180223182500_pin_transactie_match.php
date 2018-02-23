<?php


use Phinx\Migration\AbstractMigration;

class PinTransactieMatch extends AbstractMigration {
	public function up() {
		$this->query(<<<SQL
CREATE TABLE pin_transactie_match (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`reden` ENUM('match','verkeerd bedrag','missende transactie','missende bestelling') NOT NULL,
	`transactie_id` INT(11) NULL DEFAULT NULL,
	`bestelling_id` INT(11) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
SQL
		);

		$this->table('pin_transacties')->removeColumn('bestelling_id')->save();
	}

	public function down() {
		$this->query('DROP TABLE pin_transactie_match');
		$this->table('pin_transacties')->addColumn('bestelling_id', 'integer')->save();
	}
}
