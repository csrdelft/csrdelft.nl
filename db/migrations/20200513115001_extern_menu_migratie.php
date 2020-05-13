<?php

use Phinx\Migration\AbstractMigration;

class ExternMenuMigratie extends AbstractMigration {
	public function up() {
		$externMenuId = $this->createMenuItem(0, 0, 'extern', '/');

		$verenigingMenuId = $this->createMenuItem($externMenuId, 10, 'Vereniging', '/vereniging');
		$this->createMenuItem($externMenuId, 20, 'Foto\'s', '/fotoalbum/Publiek');
		$forumMenuId = $this->createMenuItem($externMenuId, 30, 'Forum', '/forum');
		$this->createMenuItem($externMenuId, 40, 'Lid worden?', '/lidworden');
		$contactMenuId = $this->createMenuItem($externMenuId, 50, 'Contact', '/contact');

		// Vereniging menu
		$this->createMenuItem($verenigingMenuId, 10, 'Geloof', '/vereniging/geloof');
		$this->createMenuItem($verenigingMenuId, 20, 'Vorming', '/vereniging/vorming');
		$this->createMenuItem($verenigingMenuId, 30, 'Gezelligheid', '/vereniging/gezelligheid');
		$this->createMenuItem($verenigingMenuId, 40, 'Sport', '/vereniging/sport');
		$this->createMenuItem($verenigingMenuId, 50, 'Ontspanning', '/vereniging/ontspanning');

		// Forum menu
		$this->createMenuItem($forumMenuId, 10, 'Kamers Zoeken en Aanbieden', '/forum/deel/12');

		// Contact menu
		$this->createMenuItem($contactMenuId, 10, 'Bedrijven', '/contact/bedrijven');
	}

	private function createMenuItem($parentId, $volgorde, $tekst, $link) {
		$this->table('menus')->insert([
			'parent_id' => $parentId,
			'volgorde' => $volgorde,
			'tekst' => $tekst,
			'link' => $link,
			'rechten_bekijken' => 'P_PUBLIC',
			'zichtbaar' => true,
		])->save();

		return $this->getAdapter()->getConnection()->lastInsertId();
	}

	public function down() {
		// sorry
	}
}
