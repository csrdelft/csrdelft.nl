<?php

use Phinx\Migration\AbstractMigration;

class MaalCieRouterRefactor extends AbstractMigration {
	public function up() {
		$this->query("UPDATE menus SET  link = '/maaltijden/beheer' WHERE link = '/maaltijdenbeheer'");
		$this->query("UPDATE menus SET  link = '/maaltijden/ketzer' WHERE link = '/maaltijdenketzer'");
		$this->query("UPDATE menus SET  link = '/maaltijden/abonnementen' WHERE link = '/maaltijdenabonnementen'");
		$this->query("UPDATE menus SET  link = '/maaltijden/beheer/archief' WHERE link = '/maaltijdenbeheer/archief'");
		$this->query("UPDATE menus SET  link = '/fiscaat/saldo' WHERE link = '/maaltijdenmaalciesaldi'");
		$this->query("UPDATE menus SET  link = '/maaltijden/abonnementen/beheer' WHERE link = '/maaltijdenabonnementenbeheer'");
		$this->query("UPDATE menus SET  link = '/corvee/beheer' WHERE link = '/corveebeheer'");
		$this->query("UPDATE menus SET  link = '/corvee/rooster' WHERE link = '/corveerooster'");
		$this->query("UPDATE menus SET  link = '/corvee/voorkeuren' WHERE link = '/corveevoorkeuren'");
		$this->query("UPDATE menus SET  link = '/corvee/functies' WHERE link = '/corveefuncties'");
		$this->query("UPDATE menus SET  link = '/corvee/vrijstellingen' WHERE link = '/corveevrijstellingen'");
		$this->query("UPDATE menus SET  link = '/corvee/voorkeuren/beheer' WHERE link = '/corveevoorkeurenbeheer'");
		$this->query("UPDATE menus SET  link = '/corvee/punten' WHERE link = '/corveepuntenbeheer'");
	}

	public function down() {

	}
}
