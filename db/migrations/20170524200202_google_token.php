<?php

use Phinx\Migration\AbstractMigration;

class GoogleToken extends AbstractMigration
{
    /**
     * Leg GoogleToken tabel aan.
     */
    public function up()
    {
        $this->execute(<<<SQL
CREATE TABLE `GoogleToken` (
  `uid` varchar(4) NOT NULL,
  `token` varchar(255) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SQL
);
    }

    /**
     * Verwijder GoogleToken tabel.
     */
    public function down()
    {
        $this->execute('DROP TABLE `GoogleToken`;');
    }
}
