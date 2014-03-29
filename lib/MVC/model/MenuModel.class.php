<?php

/**
 * MenuModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class MenuModel extends PersistenceModel {

	protected static $instance;
	protected static $orm = 'MenuItem';

	/**
	 * Lijst van alle menus.
	 * 
	 * @return array
	 */
	public function getAlleMenus() {
		$sql = 'SELECT tekst FROM menus WHERE parent_id = 0';
		$query = Database::instance()->prepare($sql);
		$query->execute();
		return $query->fetchAll(PDO::FETCH_COLUMN, 0);
	}

	/**
	 * Haalt alle menu-items op (die zichtbaar zijn voor de gebruiker).
	 * Filtert de menu-items met de permissies van het ingelogede lid.
	 * 
	 * @param string $menu_naam
	 * @param boolean $admin
	 * @return MenuItem[]
	 */
	public function getMenuTree($menu_naam, $admin = false) {
		// get root
		$where = 'parent_id = 0 AND tekst = ?';
		$params = array($menu_naam);
		$root = $this->find($where, $params, 'parent_id ASC, prioriteit ASC');
		if (sizeof($root) <= 0) {
			$item = $this->newMenuItem(0);
			$item->tekst = $menu_naam;
			return $item;
		}
		$this->getChildren($root[0], $admin);
		return $root[0];
	}

	public function getChildren(MenuItem $item, $admin = false) {
		$where = 'parent_id = ?' . ($admin ? '' : ' AND zichtbaar = true');
		$item->children = $this->find($where, array($item->item_id), 'prioriteit ASC');
		foreach ($item->children as $i => $child) {
			if (!$admin AND !LoginLid::instance()->hasPermission($child->rechten_bekijken)) {
				unset($item->children[$i]);
			} else {
				$this->getChildren($child, $admin);
			}
		}
	}

	public function getMenuItem($id) {
		return $this->retrieveByPrimaryKey(array($id));
	}

	public function newMenuItem($parent_id) {
		$item = new MenuItem();
		$item->parent_id = (int) $parent_id;
		$item->prioriteit = 0;
		$item->link = '/';
		$item->rechten_bekijken = 'P_NOBODY';
		$item->zichtbaar = true;
		return $item;
	}

	public function removeMenuItem(MenuItem $item) {
		$db = Database::instance();
		try {
			$db->beginTransaction();
			// give new parent to otherwise future orphans
			$properties = array('parent_id' => $item->parent_id);
			$count = Database::sqlUpdate($this->orm_entity->getTableName(), $properties, 'parent_id = :oldid', array(':oldid' => $item->item_id));
			$this->delete($item);
			$db->commit();
			setMelding($count . ' menu-items nieuwe parent gegeven.', 2);
		} catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

}
