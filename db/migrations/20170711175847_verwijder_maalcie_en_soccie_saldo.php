<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 20170711
 */
class VerwijderMaalcieEnSoccieSaldo extends AbstractMigration {
	/**
	 * Verwijder de maalcieSaldo en soccieSaldo kolommen.
	 */
	public function change() {
		$this->table('profielen')
			->removeColumn('maalcieSaldo')
			->removeColumn('soccieSaldo')
			->update();
	}
}
