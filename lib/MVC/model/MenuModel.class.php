<?php

require_once 'MVC/model/entity/MenuItem.class.php';

/**
 * MenuModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class MenuModel extends PersistenceModel {

	/**
	 * Lijst van alle menus.
	 * 
	 * @return array
	 */
	public function getAlleMenus() {
		$sql = 'SELECT DISTINCT menu FROM menus';
		$query = Database::instance()->prepare($sql);
		$query->execute();
		return $query->fetchAll(PDO::FETCH_COLUMN, 0);
	}

	public function getMenuItem($id) {
		$item = new MenuItem();
		$item->menu_id = $id;
		return $this->retrieve($item);
	}

	/**
	 * Haalt alle menu-items op (die zichtbaar zijn).
	 * 
	 * @param string $menu
	 * @param boolean $zichtbaar
	 * @return MenuItem[]
	 */
	public function getMenuItems($menu, $zichtbaar = null) {
		$where = 'menu = ?';
		$params = array($menu);
		if ($zichtbaar !== null) {
			$where .= ' AND zichtbaar = ' . ($zichtbaar ? 'true' : 'false');
		}
		return $this->find(new MenuItem(), $where, $params, 'parent_id ASC, prioriteit ASC');
	}

	/**
	 * Haalt alle menu-items op die zichtbaar zijn voor het ingelogde lid.
	 * 
	 * @param string $menu
	 * @return MenuItem[]
	 */
	public function getMenuItemsVoorLid($menu) {
		return $this->filterMenuItems($this->getMenuItems($menu, true));
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
		$root->menu_id = '0';
		$root->tekst = $menu_naam;
		$root->menu = $menu_naam;
		$root->addChildren($menuitems); // recursive
		$root->id = false;
		return $root;
	}

	public function wijzigProperty($id, $property, $value) {
		$rowcount = Database::sqlUpdate('menus', array($property => $value), 'menu_id = :id', array(':id' => $id));
		if ($rowcount !== 1) {
			throw new Exception('wijzigProperty rowCount=' . $rowcount);
		}
		return $this->getMenuItem($id);
	}

	public function saveMenuItem(MenuItem $item) {
		if (is_int($item->id) AND $item->id > 0) {
			$this->update($item);
		} else {
			$id = $this->create($item);
			$item->id = intval($id);
		}
	}

	public function deleteMenuItem($id) {
		$item = $this->getMenuItem($id);
		$this->delete($item);
		return $item;
	}

	public function delete(PersistentEntity $item) {
		$db = Database::instance();
		try {
			$db->beginTransaction();
			// give new parent to otherwise future orphans
			$properties = array('parent_id' => $item->parent_id);
			$count = $this->update($properties, 'parent_id = :oldid', array(':oldid' => $item->parent_id));
			$this->delete($item);
			$db->commit();
			return $count;
		} catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

}
