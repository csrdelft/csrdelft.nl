<?php


use Phinx\Migration\AbstractMigration;

class ForumDeelMeldingen extends AbstractMigration
{
    public function change()
    {
		$this->table('forum_delen_meldingen')
			->addColumn('forum_id', 'integer')
			->addColumn('uid', 'integer')
			->create();
    }
}
