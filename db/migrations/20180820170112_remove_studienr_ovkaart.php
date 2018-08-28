<?php


use Phinx\Migration\AbstractMigration;

class RemoveStudienrOvkaart extends AbstractMigration
{
    public function change()
    {
		$this->table('profielen')->removeColumn("ovkaart")->removeColumn("studienr")->update();
    }
}
