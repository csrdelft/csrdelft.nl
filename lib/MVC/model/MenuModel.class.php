<?php

require_once 'MVC/model/entity/MenuItem.class.php';

/**
 * MenuModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class MenuModel extends PersistenceModel {

	public function __construct() {
		parent::__construct(new MenuItem());
	}

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
		if (sizeof($root) > 0) {
			$this->getChildren($root[0], $admin);
			return $root[0];
		}
		return null;
	}

	public function getChildren(MenuItem $item, $admin = false) {
		$where = 'parent_id = ?' . ($admin ? '' : ' AND zichtbaar = true');
		$item->children = $this->find($where, array($item->item_id), 'prioriteit ASC');
		foreach ($item->children as $i => $child) {
			if (!$admin AND !LoginLid::instance()->hasPermission($item->rechten_bekijken)) {
				unset($item->children[$i]);
			} else {
				$this->getChildren($child, $admin);
			}
		}
	}

	public function getMenuItem($id) {
		$item = $this->retrieveByPrimaryKey(array($id));
		if (!$item) {
			throw new Exception('Menu-item ' . $id . ' bestaat niet');
		}
		return $item;
	}

	public function newMenuItem($parent_id) {
		$item = new MenuItem();
		$item->parent_id = intval($parent_id);
		$item->prioriteit = 0;
		$item->link = '/';
		$item->rechten_bekijken = 'P_NOBODY';
		$item->zichtbaar = true;
		return $item;
	}

	public function delete(PersistentEntity $item) {
		$db = Database::instance();
		try {
			$db->beginTransaction();
			// give new parent to otherwise future orphans
			$properties = array('parent_id' => $item->parent_id);
			$count = Database::sqlUpdate($this->orm_entity->getTableName(), $properties, 'parent_id = :oldid', array(':oldid' => $item->item_id));
			parent::delete($item);
			$db->commit();
			setMelding($count . ' menu-items nieuwe parent gegeven.', 2);
		} catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

}
