<?php

/**
 * MenuModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class MenuModel extends PersistenceModel {

	const orm = 'MenuItem';

	protected static $instance;

	/**
	 * Lijst van alle menus.
	 * 
	 * @return PDOStatement
	 */
	public function getAlleMenus() {
		$sql = 'SELECT tekst FROM menus WHERE parent_id = 0';
		$query = Database::instance()->prepare($sql);
		$query->execute();
		$query->setFetchMode(PDO::FETCH_COLUMN, 0);
		return $query;
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
		$root = $this->find($where, $params)->fetch(); // should be only 1
		if (!$root) {
			$item = $this->newMenuItem(0);
			$item->tekst = $menu_naam;
			return $item;
		}
		$this->getChildren($root, $admin);
		return $root;
	}

	public function getChildren(MenuItem $parent, $admin = false) {
		$where = 'parent_id = ?' . ($admin ? '' : ' AND zichtbaar = true');
		$parent->children = $this->find($where, array($parent->item_id), 'prioriteit ASC')->fetchAll();
		$child_active = false;
		foreach ($parent->children as $i => $child) {
			if (!$admin AND ! $child->magBekijken()) {
				unset($parent->children[$i]);
				continue;
			}
			$child->active = startsWith(REQUEST_URI, $child->link);
			$child_active |= $child->active;
			$this->getChildren($child, $admin);
		}
		$parent->active |= $child_active; // make parent of active child also active
	}

	public function getMenuItem($id) {
		return $this->retrieveByPrimaryKey(array($id));
	}

	public function newMenuItem($parent_id) {
		$item = new MenuItem();
		$item->parent_id = $parent_id;
		$item->prioriteit = 0;
		$item->link = '/';
		$item->rechten_bekijken = 'P_PUBLIC';
		$item->zichtbaar = false;
		return $item;
	}

	public function removeMenuItem(MenuItem $item) {
		$db = Database::instance();
		try {
			$db->beginTransaction();
			// give new parent to otherwise future orphans
			$properties = array('parent_id' => $item->parent_id);
			$orm = self::orm;
			$count = Database::sqlUpdate($orm::getTableName(), $properties, 'parent_id = :oldid', array(':oldid' => $item->item_id));
			$this->delete($item);
			$db->commit();
			SimpleHTML::setMelding($count . ' menu-items nieuwe parent gegeven.', 2);
		} catch (Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

}
