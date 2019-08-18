<?php

use Phinx\Migration\AbstractMigration;

class FotoAlbumCleanup extends AbstractMigration {
	public function down() {
		$this->query("UPDATE fotos SET subdir = CONCAT('fotoalbum/', subdir, '/')");
		$this->query("UPDATE fotoalbums SET subdir = CONCAT('fotoalbum/', subdir, '/')");
		$this->query("UPDATE foto_tags SET refuuid = CONCAT('fotoalbum/', refuuid)");
	}

	public function up() {
		// Verwijder dubbel publiek album
		$this->query("DELETE FROM fotoalbums WHERE subdir = 'fotoalbum/Publiek/'");

		$this->query("UPDATE fotos SET subdir = TRIM(LEADING 'fotoalbum/' FROM TRIM(TRAILING '/' FROM subdir))");
		$this->query("UPDATE fotoalbums SET subdir = TRIM(LEADING 'fotoalbum/' FROM TRIM(TRAILING '/' FROM subdir))");
		$this->query("UPDATE foto_tags SET refuuid = TRIM(LEADING 'fotoalbum/' FROM refuuid)");
	}
}
