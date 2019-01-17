<?php


use Phinx\Migration\AbstractMigration;

class ForumDeelMeldingen extends AbstractMigration
{
    public function change()
    {
		$this->table('forum_delen_meldingen', ['id' => false, 'primary_key' => ['forum_id', 'uid']])
			->addColumn('forum_id', 'integer')
			->addColumn('uid', 'string', ['length' => 4])
			->create();
    }
}
