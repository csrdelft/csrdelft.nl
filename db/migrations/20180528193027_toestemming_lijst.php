<?php


use Phinx\Migration\AbstractMigration;

class ToestemmingLijst extends AbstractMigration {
	public function up() {
		$this->query(<<<SQL
INSERT INTO `menus` (`parent_id`, `volgorde`, `tekst`, `link`, `rechten_bekijken`, `zichtbaar`) VALUES (3, 0, 'Overzicht van toestemming', '/toestemming/lijst', 'P_LEDEN_MOD', 1);
INSERT INTO `menus` (`parent_id`, `volgorde`, `tekst`, `link`, `rechten_bekijken`, `zichtbaar`) VALUES (3, 0, 'Overzicht van toestemming (foto)', '/toestemming/lijst_foto', 'P_ALBUM_MOD', 1);
SQL
		);
	}

	public function down() {
		$this->query(<<<SQL
DELETE FROM `menus` WHERE `link` IN ('/toestemming/lijst', '/toestemming/lijst_foto');
SQL
		);
	}
}
