<?php

use Phinx\Migration\AbstractMigration;

class FiscaatRechten extends AbstractMigration {
	public function up() {
		$this->query("ALTER TABLE accounts CHANGE perm_role perm_role ENUM('R_NOBODY','R_ETER','R_OUDLID','R_LID','R_BASF','R_MAALCIE','R_BESTUUR','R_PUBCIE','R_FISCAAT','R_VLIEGER') NOT NULL;");
	}

	public function down() {
		$this->query("ALTER TABLE accounts CHANGE perm_role perm_role ENUM('R_NOBODY','R_ETER','R_OUDLID','R_LID','R_BASF','R_MAALCIE','R_BESTUUR','R_PUBCIE','R_VLIEGER') NOT NULL;");
	}
}
