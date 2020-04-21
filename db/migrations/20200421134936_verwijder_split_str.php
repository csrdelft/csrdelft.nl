<?php

use Phinx\Migration\AbstractMigration;

class VerwijderSplitStr extends AbstractMigration {
	public function up() {
		$this->query(<<<'SQL'
DROP FUNCTION IF EXISTS `SPLIT_STR`
SQL
		);
	}

	public function down() {
		$this->query(<<<SQL
CREATE DEFINER=`csrdelft`@`localhost`
FUNCTION `SPLIT_STR` (`x` VARCHAR(255), `delim` VARCHAR(12), `pos` INT) RETURNS VARCHAR(255)
CHARSET utf8 RETURN REPLACE(SUBSTRING(SUBSTRING_INDEX(x, delim, pos), LENGTH(SUBSTRING_INDEX(x, delim, pos -1)) + 1), delim, '')
SQL
		);
	}
}
