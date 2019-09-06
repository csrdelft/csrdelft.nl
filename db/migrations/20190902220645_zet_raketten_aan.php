<?php

use Phinx\Migration\AbstractMigration;

class ZetRakettenAan extends AbstractMigration {
	public function up() {
		$this->query(<<<SQL
INSERT INTO lidinstellingen (uid, module, instelling_id, waarde)
SELECT profielen.uid, 'layout', 'raket', '2019'
    FROM profielen
    LEFT OUTER JOIN lidinstellingen ON profielen.uid = lidinstellingen.uid AND module = 'layout' AND instelling_id = 'raket'
    WHERE (status = "S_LID" OR status = "S_NOVIET" OR status = "S_GASTLID")
      AND lidinstellingen.uid IS NULL
SQL
		);
	}

	public function down() {
		$this->query(<<<SQL
DELETE FROM lidinstellingen
    WHERE module = 'layout'
    AND instelling_id = 'raket'
SQL
		);
	}
}
