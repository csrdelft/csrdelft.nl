<?php
use Phinx\Migration\AbstractMigration;

/**
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class ProfielModelCleanup extends AbstractMigration
{
	/**
	 * Verwijder verwijder velden icq, msn, skype, jid, soccieID en createTerm uit profiel
	 */
	public function change() {
		$this->table('profielen')
			->removeColumn('icq')
			->removeColumn('msn')
			->removeColumn('skype')
			->removeColumn('jid')
			->removeColumn('soccieID')
			->removeColumn('createTerm')
			->update();
	}
}
