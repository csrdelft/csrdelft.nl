<?php

use Phinx\Migration\AbstractMigration;

class CmsPaginaOverzichtPaginaToevoegen extends AbstractMigration
{
    public function down()
    {
    	$this->query("DELETE FROM menus WHERE link = '/pagina';");
    }

    public function up() {
    	$this->query("INSERT INTO menus (parent_id, volgorde, tekst, link, rechten_bekijken, zichtbaar) VALUES (3 , 0, 'Pagina overzicht', '/pagina', 'P_LOGGED_IN', 1)");
		}
}
