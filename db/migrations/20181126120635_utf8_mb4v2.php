<?php


use Phinx\Migration\AbstractMigration;

class Utf8Mb4v2 extends AbstractMigration
{
    public function up()
    {
			$this->query(<<<'SQL'
ALTER TABLE ketzers CHANGE naam naam varchar(191) NOT NULL;
ALTER TABLE ketzers CHANGE familie familie varchar(191) NOT NULL;
ALTER TABLE activiteiten CHANGE naam naam varchar(191) NOT NULL;
ALTER TABLE activiteiten CHANGE familie familie varchar(191) NOT NULL;
ALTER TABLE besturen CHANGE naam naam varchar(191) NOT NULL;
ALTER TABLE besturen CHANGE familie familie varchar(191) NOT NULL;
ALTER TABLE kringen CHANGE naam naam varchar(191) NOT NULL;
ALTER TABLE kringen CHANGE familie familie varchar(191) NOT NULL;
ALTER TABLE lichtingen CHANGE naam naam varchar(191) NOT NULL;
ALTER TABLE lichtingen CHANGE familie familie varchar(191) NOT NULL;
ALTER TABLE werkgroepen CHANGE naam naam varchar(191) NOT NULL;
ALTER TABLE werkgroepen CHANGE familie familie varchar(191) NOT NULL;
SQL
);
    }

    public function down() {
			$this->query(<<<'SQL'
ALTER TABLE ketzers CHANGE naam naam varchar(255) NOT NULL;
ALTER TABLE ketzers CHANGE familie familie varchar(255) NOT NULL;
ALTER TABLE activiteiten CHANGE naam naam varchar(255) NOT NULL;
ALTER TABLE activiteiten CHANGE familie familie varchar(255) NOT NULL;
ALTER TABLE besturen CHANGE naam naam varchar(255) NOT NULL;
ALTER TABLE besturen CHANGE familie familie varchar(255) NOT NULL;
ALTER TABLE kringen CHANGE naam naam varchar(255) NOT NULL;
ALTER TABLE kringen CHANGE familie familie varchar(255) NOT NULL;
ALTER TABLE lichtingen CHANGE naam naam varchar(255) NOT NULL;
ALTER TABLE lichtingen CHANGE familie familie varchar(255) NOT NULL;
ALTER TABLE werkgroepen CHANGE naam naam varchar(255) NOT NULL;
ALTER TABLE werkgroepen CHANGE familie familie varchar(255) NOT NULL;
SQL
			);
		}
}
