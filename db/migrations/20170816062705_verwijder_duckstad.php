<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 20170816
 */
class VerwijderDuckstad extends AbstractMigration
{
    /**
     * Verwijder duck
     */
    public function up()
    {
        $this->table('profielen')
            ->removeColumn('duckname')
            ->update();
    }
}
