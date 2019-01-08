<?php


use Phinx\Migration\AbstractMigration;

class VoegNiveauToeAanForumDraadVolgen extends AbstractMigration
{
    public function change()
    {
        $this->table('forum_draden_volgen')
            ->addColumn('niveau', 'enum', [
                'values' => ['nooit', 'vermelding', 'altijd'],
                'default' => 'altijd'
            ])
            ->save();
    }
}
