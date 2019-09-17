<?php

use Phinx\Migration\AbstractMigration;

class CourantCleanup extends AbstractMigration
{
    public function up() {
    	$this->table('courantcache')->drop()->save();
		}

		public function down() {
    	// Niets
		}
}
