<?php

use Phinx\Migration\AbstractMigration;

/**
 * Class DocumentenRefactor.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class DocumentenRefactor extends AbstractMigration {
	/**
	 * Hernoem tabellen en kolomnamen.
	 */
	public function change() {
		$this->table('document')
			->rename('Document')
			->renameColumn('ID', 'id')
			->renameColumn('catID', 'categorie_id')
			->update();

		$this->table('documentcategorie')
			->rename('DocumentCategorie')
			->renameColumn('ID', 'id')
			->update();
	}
}
