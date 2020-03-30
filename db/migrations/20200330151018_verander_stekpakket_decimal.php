<?php

use Phinx\Migration\AbstractMigration;

class VeranderStekpakketDecimal extends AbstractMigration {
	public function up() {
		$this->table('stekpakket')
			->changeColumn('prijs', 'decimal', ['precision' => 4, 'scale' => 2])
			->save();
	}
}
