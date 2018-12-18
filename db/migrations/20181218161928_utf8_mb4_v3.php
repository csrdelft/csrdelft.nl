<?php


use Phinx\Migration\AbstractMigration;

class Utf8Mb4V3 extends AbstractMigration
{
    public function up()
    {
    	$this->query(<<<SQL
ALTER TABLE woonoorden CHANGE naam naam varchar(191) NOT NULL;
ALTER TABLE woonoorden CHANGE familie familie varchar(191) NOT NULL;
SQL
);
    }

    public function down() {
			$this->query(<<<SQL
ALTER TABLE woonoorden CHANGE naam naam varchar(255) NOT NULL;
ALTER TABLE woonoorden CHANGE familie familie varchar(255) NOT NULL;
SQL
			);
		}
}
