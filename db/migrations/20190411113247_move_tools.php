<?php


use Phinx\Migration\AbstractMigration;

class MoveTools extends AbstractMigration {
	public function down() {
		// sorry
	}

	public function up() {
		$this->query("UPDATE menus SET link='/tools/roodschopper' WHERE link='/tools/roodschopper.php';");
		$this->query("UPDATE menus SET link='/tools/streeplijst' WHERE link='/tools/streeplijst.php';");
		$this->query("UPDATE menus SET link='/tools/query?id=113' WHERE link='/tools/query.php?id=113';");
		$this->query("UPDATE menus SET link='/tools/query?id=116' WHERE link='/tools/query.php?id=116';");
		$this->query("UPDATE menus SET link='/tools/query' WHERE link='/tools/query.php';");
		$this->query("UPDATE menus SET link='/tools/phpinfo' WHERE link='/tools/phpinfo.php';");
		$this->query("UPDATE menus SET link='/tools/memcachestats' WHERE link='/tools/memcachestats.php';");
		$this->query("DELETE FROM menus WHERE link='/tools/flushcache.php';");
		$this->query("DELETE FROM menus WHERE link='/tools/dump.php';");
		$this->query("UPDATE menus SET link='/tools/verticalelijsten' WHERE link='/tools/verticalelijsten.php';");
		$this->query("UPDATE menus SET link='/tools/stats' WHERE link='/tools/stats.php';");
		$this->query("UPDATE menus SET link='/tools/syncldap' WHERE link='/tools/syncldap.php';");
		$this->query("UPDATE menus SET link='/tools/admins' WHERE link='/tools/admins.php';");
		$this->query("UPDATE menus SET link='/tools/novieten' WHERE link='/tools/novieten.php';");
	}
}
