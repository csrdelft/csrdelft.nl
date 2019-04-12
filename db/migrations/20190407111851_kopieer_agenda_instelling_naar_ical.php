<?php


use Phinx\Migration\AbstractMigration;

class KopieerAgendaInstellingNaarIcal extends AbstractMigration
{
    public function up()
    {
			$this->query("
					INSERT INTO `lidinstellingen` (uid, module, instelling_id, waarde)
					SELECT uid, module, 'toonVerjaardagenICal', waarde
					FROM lidinstellingen
					WHERE module = 'agenda' AND instelling_id = 'toonVerjaardagen' AND waarde = 'nee'
					AND uid NOT IN (SELECT uid FROM `lidinstellingen` WHERE instelling_id = 'toonVerjaardagenICal')
				");
    }
}
