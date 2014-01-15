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
		$sql = 'SELECT DISTINCT menu_naam FROM menus';
		$query = Database::instance()->prepare($sql);
		$query->execute();
		return $query->fetchAll(PDO::FETCH_COLUMN, 0);
	}

	/**
	 * Haalt alle menu-items op (die zichtbaar zijn).
	 * 
	 * @param string $menu_naam
	 * @param boolean $zichtbaar
	 * @return MenuItem[]
	 */
	public function getMenuItems($menu_naam, $zichtbaar = null) {
		$where = 'menu_naam = ?';
		$params = array($menu_naam);
		if ($zichtbaar !== null) {
			$where .= ' AND zichtbaar = ' . ($zichtbaar ? 'true' : 'false');
		}
		return $this->find($where, $params, 'parent_id ASC, prioriteit ASC');
	}

	/**
	 * Haalt alle menu-items op die zichtbaar zijn voor het ingelogde lid.
	 * 
	 * @param string $menu_naam
	 * @return MenuItem[]
	 */
	public function getMenuItemsVoorLid($menu_naam) {
		return $this->filterMenuItems($this->getMenuItems($menu_naam, true));
	}

	/**
	 * Filtert de menu-items met de permissies van het ingelogede lid.
	 * 
	 * @param MenuItem[] $menuitems
	 * @return MenuItem[]
	 */
	private function filterMenuItems(array $menuitems) {
		$result = array();
		foreach ($menuitems as $i => $item) {
			if (LoginLid::instance()->hasPermission($item->permission)) {
				$result[$i] = $item;
			}
			unset($menuitems[$i]);
		}
		return $result;
	}

	public function buildMenuTree($menu_naam, $menuitems) {
		$root = new MenuItem();
		$root->item_id = '0';
		$root->tekst = $menu_naam;
		$root->menu_naam = $menu_naam;
		$root->addChildren($menuitems); // recursive
		$root->item_id = 0;
		return $root;
	}

	public function wijzigProperty($id, $property, $value) {
		$rowcount = Database::sqlUpdate('menus', array($property => $value), 'item_id = :id', array(':id' => $id));
		if ($rowcount !== 1) {
			throw new Exception('wijzigProperty rowCount=' . $rowcount);
		}
		return $this->retrieveByPrimaryKey(array($id));
	}

	public function saveMenuItem(MenuItem $item) {
		if (is_int($item->item_id) AND $item->item_id > 0) {
			$this->update($item);
		} else {
			$id = $this->create($item);
			$item->item_id = intval($id);
		}
	}

	public function deleteMenuItem($id) {
		$item = $this->retrieveByPrimaryKey(array($id));
		if (!$item) {
			throw new Exception('Menu-item ' . $id . ' bestaat niet');
		}
		$this->delete($item);
		return $item;
	}

	public function delete(PersistentEntity $item) {
		$db = Database::instance();
		try {
			$db->beginTransaction();
			// give new parent to otherwise future orphans
			$properties = array('parent_id' => $item->parent_id);
			$count = Database::sqlUpdate('menus', $properties, 'parent_id = :oldid', array(':oldid' => $item->parent_id));
			setMelding($count . ' menu-items nieuwe parent gegeven.', 2);
			parent::delete($item);
			$db->commit();
			return $count;
		} catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

}
