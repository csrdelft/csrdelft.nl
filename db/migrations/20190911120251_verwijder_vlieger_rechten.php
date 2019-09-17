<?php

use Phinx\Migration\AbstractMigration;

class VerwijderVliegerRechten extends AbstractMigration {
	public function up() {
		$this->query('UPDATE accounts SET perm_role = "R_OUDLID" WHERE perm_role = "R_VLIEGER";');
		$this->query("ALTER TABLE accounts CHANGE perm_role perm_role ENUM('R_NOBODY','R_ETER','R_OUDLID','R_LID','R_BASF','R_MAALCIE','R_BESTUUR','R_PUBCIE','R_FISCAAT','R_FORUM_MOD') NOT NULL;");
	}

	public function down() {
		$this->query("ALTER TABLE accounts CHANGE perm_role perm_role ENUM('R_NOBODY','R_ETER','R_OUDLID','R_LID','R_BASF','R_MAALCIE','R_BESTUUR','R_PUBCIE','R_FISCAAT','R_VLIEGER','R_FORUM_MOD') NOT NULL;");
	}
}
