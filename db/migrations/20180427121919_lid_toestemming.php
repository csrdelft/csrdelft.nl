<?php


use Phinx\Migration\AbstractMigration;

class LidToestemming extends AbstractMigration
{
    public function up()
    {
        $this->query(<<<SQL
CREATE TABLE `lidtoestemmingen` (
	`uid` VARCHAR(4) NOT NULL,
	`module` VARCHAR(255) NOT NULL,
	`instelling_id` VARCHAR(255) NOT NULL,
	`waarde` TEXT NULL,
	PRIMARY KEY (`uid`, `module`, `instelling_id`),
	INDEX `uid` (`uid`),
    CONSTRAINT `lidtoestemmingen_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
SQL
);
    }

    public function down()
    {
        $this->query('DROP TABLE `lidtoestemmingen`;');
    }
}
