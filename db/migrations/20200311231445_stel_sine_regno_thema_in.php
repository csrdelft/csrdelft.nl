<?php

use Phinx\Migration\AbstractMigration;

class StelSineRegnoThemaIn extends AbstractMigration {
	public function up() {
		if (time() >= strtotime('13-03-2020 15:00') && time() <= strtotime('14-03-2020')) {
			// Voorkomt dat dit over 3 jaar nog steeds op je dev omgeving gebeurt
			$thema = 'sineregno';
			$this->query('
				UPDATE lidinstellingen SET waarde = ' . $thema . '
				WHERE module = "layout" AND instelling_id = "opmaak"
				AND uid IN (SELECT uid FROM profielen WHERE status IN ("S_LID", "S_GASTLID", "S_NOVIET"))
			');
		}
	}

	public function down() {
		// Kan niet gerevert worden
	}
}
