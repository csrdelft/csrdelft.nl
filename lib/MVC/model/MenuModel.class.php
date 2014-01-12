<?php

require_once 'MVC/model/entity/MenuItem.class.php';

/**
 * MenuModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class MenuModel extends PaginationModel {

	public function getAlleMenus() {
		$sql = 'SELECT DISTINCT menu_naam FROM menu';
		$params = array();
		$db = Database::instance();
		$query = $db->prepare($sql, $params);
		$query->execute($params);
		return $query->fetchAll(PDO::FETCH_COLUMN, 0);
	}

	/**
	 * Haalt alle menu-items op (die zichtbaar zijn)
	 * @param string $menu
	 * @param boolean $zichtbaar
	 * @return MenuItem[]
	 */
	public function getMenuItems($menu, $zichtbaar) {
		$where = 'menu = ?';
		$params = array($menu);
		if ($zichtbaar === true OR $zichtbaar === false) {
			$where .= ' AND zichtbaar = ?';
			$params[] = $zichtbaar;
		}
		return $this->select($where, $params);
	}

	public function getMenuItemsVoorLid($menu) {
		return $this->filterMenuItems($this->getMenuItems($menu, true));
	}

	/**
	 * Filtert de menu-items met de permissies van het ingelogede lid
	 * @param MenuItem[] $menuitems
	 * @return MenuItem[]
	 */
	protected function filterMenuItems($menuitems) {
		$result = array();
		foreach ($menuitems as $i => $item) {
			if (\LoginLid::instance()->hasPermission($item->getPermission())) {
				$result[$i] = $item;
			}
			unset($menuitems[$i]);
		}
		return $result;
	}

	public function getMenuTree($menu, &$menuitems) {
		$root = new MenuItem();
		$root->tekst = $menu;
		$root->menu_naam = $menu;
		$root->addChildren($menuitems);
		return $root;
	}

	protected function load($where = null, array $params = array(), $assoc = false) {
		if (is_int($where)) {
			return $this->get('id = ?', array($where));
		}
		$list = $this->select($where, $params, 'parent_id ASC, prioriteit ASC');
		if (!$assoc) {
			return $list;
		}
		$result = array();
		foreach ($list as $i => $menuitem) {
			$result[$menuitem->id] = $menuitem;
			unset($list[$i]);
		}
		return $result;
	}

	public function saveMenuItem(MenuItem &$menuitem) {
		$properties = $menuitem->getPersistingValues();
		if (is_int($menuitem->id) && $menuitem->id > 0) { // update existing
			$count = $this->update($properties, 'id = :id', array(':id' => $menuitem->id));
			if ($count !== 1) {
				throw new Exception('Update row count: ' . $count);
			}
		} else { // insert new
			$menuitem->id = $this->insert($properties);
		}
	}

	public function saveProperty($id, $key, $value) {
		$this->update(array($key => $value), 'id = :id', array(':id' => $id));
	}

	public function deleteMenuItem(MenuItem $menuitem) {
		$db = Database::instance();
		try {
			$db->beginTransaction();
			foreach ($menuitem->children as $child) { // give new parent to otherwise future orphans
				$properties = array('parent_id' => $menuitem->parent_id);
				$this->update($properties, 'parent_id = :oldid', array(':oldid' => $menuitem->parent_id));
			}
			$this->delete('id = ?', array($menuitem->id));
			$db->commit();
		} catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

}
