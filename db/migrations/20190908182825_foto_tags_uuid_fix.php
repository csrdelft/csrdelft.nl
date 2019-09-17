<?php

use Phinx\Migration\AbstractMigration;

class FotoTagsUuidFix extends AbstractMigration
{
	public function up() {

		$this->query(<<<SQL
INSERT IGNORE INTO foto_tags (refuuid, keyword, door, wanneer, x, y, size) 
SELECT REPLACE(ft.refuuid, '@Foto.csrdelft.nl', '@CsrDelft\\\\model\\\\entity\\\\fotoalbum\\\\Foto.csrdelft.nl') as rf, keyword, door, wanneer, x, y, `size` 
FROM foto_tags as ft
WHERE refuuid LIKE '%@Foto.csrdelft.nl'
SQL
);
		$this->query(<<<SQL
DELETE FROM foto_tags
WHERE refuuid LIKE '%@Foto.csrdelft.nl'
SQL
);
	}

	public function down() {}
}
