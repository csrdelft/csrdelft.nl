<?php

use Phinx\Migration\AbstractMigration;

class BiebDoctrine extends AbstractMigration {
	public function up() {
		$this->table('biebcategorie')
			->changeColumn('p_id', 'integer', ['null' => true])
			->update();

		$this->query("UPDATE biebcategorie SET p_id = NULL WHERE p_id = 0;");

		$this->table('biebboek')
			->changeColumn('isbn', 'string', ['null' => true])
			->changeColumn('paginas', 'integer', ['null' => true])
			->changeColumn('categorie_id', 'integer', ['null' => true])
			->update();
	}

	public function down() {
		$this->table('biebcategorie')
			->changeColumn('p_id', 'integer', ['null' => false])
			->update();

		$this->query("UPDATE biebcategorie SET p_id = 0 WHERE p_id = NULL;");

		$this->table('biebboek')
			->changeColumn('isbn', 'string', ['null' => false])
			->changeColumn('paginas', 'integer', ['null' => false])
			->changeColumn('categorie_id', 'integer', ['null' => false])
			->update();
	}
}
