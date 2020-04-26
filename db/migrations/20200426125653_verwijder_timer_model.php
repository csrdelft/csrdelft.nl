<?php

use Phinx\Migration\AbstractMigration;

class VerwijderTimerModel extends AbstractMigration
{
	public function up() {
		$this->table('execution_times')->drop()->save();
	}

	public function down() {
		// sorry
	}
}
