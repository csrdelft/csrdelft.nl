<?php

use Phinx\Migration\AbstractMigration;

class AddForumPlaatje extends AbstractMigration
{
    public function change()
    {
			$this->table('forumplaatjes')
				->addColumn('access_key', 'string', ['limit' => 192])
				->addIndex('access_key')
				->addColumn('maker', 'string', ['limit' => 4, 'null' => true])
				->addColumn('datum_toegevoegd', 'datetime')
				->addColumn('source_url', 'text', ['null' => true])
				->create();
    }
}
