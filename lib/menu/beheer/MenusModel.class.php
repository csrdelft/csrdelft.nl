<?php

require_once 'menu/beheer/MenuItem.class.php';

/**
 * MenuModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class MenusModel {

	public static function getAlleMenus() {
		return self::loadMenuItems(null, array(), true);
	}

	public static function getMenuItem($mid) {
		$items = self::loadMenuItems('menu_id = ?', array($mid), false, 1);
		return reset($items);
	}

	public static function getMenuItems($menu, $zichtbaar = true) {
		if ($zichtbaar) {
			return self::loadMenuItems('menu = ? AND zichtbaar = true', array($menu));
		}
		return self::loadMenuItems('menu = ?', array($menu));
	}

	public static function getMenuItemsVoorLid($menu) {
		$items = self::getMenuItems($menu, true);
		return self::filterMenuItems($items);
	}

	public static function getMenuTree($menu, array $items) {
		$root = new MenuItem();
		$root->setTekst($menu);
		$root->setMenu($menu);
		self::addChildren($root, $items);
		foreach ($items as $item) {
			setMelding('Parent ' . $item->getParentId() . ' bestaat niet: ' . $item->getTekst() . ' (' . $item->getMenuId() . ')', -1);
			$root->children[] = $item;
		}
		return $root;
	}

	private static function addChildren(&$parent, &$children) {
		foreach ($children as $i => $item) {
			if ($parent->getMenuId() === $item->getParentId()) { // this is the correct parent
				$parent->children[] = $item;
				unset($children[$i]); // only one parent
				self::addChildren($item, $children); // add children of children
			}
		}
	}

	/**
	 * Filtert de menu-items met de permissies van het ingelogede lid.
	 * 
	 * @param MenuItem[] $items
	 * @return MenuItem[]
	 */
	private static function filterMenuItems($items) {
		$result = array();
		foreach ($items as $i => $item) {
			if (\LoginLid::instance()->hasPermission($item->getPermission())) {
				$result[$i] = $item;
			}
		}
		return $result;
	}

	private static function loadMenuItems($where = null, $values = array(), $menusOnly = false, $limit = null) {
		if ($menusOnly) {
			$sql = 'SELECT DISTINCT menu';
		} else {
			$sql = 'SELECT menu_id, parent_id, prioriteit, tekst, link, permission, zichtbaar, menu';
		}
		$sql.= ' FROM menus';
		if ($where !== null) {
			$sql.= ' WHERE ' . $where;
		}
		$sql.= ' ORDER BY parent_id ASC, prioriteit ASC';
		if (is_int($limit) && $limit > 0) {
			$sql.= ' LIMIT ' . $limit;
		}
		$db = CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		$result = $query->fetchAll(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, 'MenuItem');
		return $result;
	}

	public static function newMenuItem($pid, $prio, $text, $link, $perm, $show, $menu) {
		$sql = 'INSERT INTO menus';
		$sql.= ' (menu_id, parent_id, prioriteit, tekst, link, permission, zichtbaar, menu)';
		$sql.= ' VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
		$values = array(null, $pid, $prio, $text, $link, $perm, $show, $menu);
		$db = CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		if ($query->rowCount() !== 1) {
			throw new \Exception('New functie faalt: $query->rowCount() =' . $query->rowCount());
		}
		return new MenuItem(intval($db->lastInsertId()), $pid, $prio, $text, $link, $perm, $show, $menu);
	}

	public static function updateMenuItem(MenuItem $item) {
		$sql = 'UPDATE menus';
		$sql.= ' SET parent_id=?, prioriteit=?, tekst=?, link=?, permission=?, zichtbaar=?, menu=?';
		$sql.= ' WHERE menu_id=?';
		$values = array(
			$item->getParentId(),
			$item->getPrioriteit(),
			$item->getTekst(),
			$item->getLink(),
			$item->getPermission(),
			$item->getIsZichtbaar(),
			$item->getMenu(),
			$item->getMenuId()
		);
		$db = CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
	}

	public static function deleteMenuItem(MenuItem $item) {
		$db = CsrPdo::instance();
		try {
			$db->beginTransaction();
			foreach ($item->children as $child) { // give new parent to otherwise future orphans
				$child->setParentId($item->getParentId());
				self::updateMenuItem($child);
			}
			$sql = 'DELETE FROM menus';
			$sql.= ' WHERE menu_id = ?';
			$values = array($item->getMenuId());
			$query = $db->prepare($sql, $values);
			$query->execute($values);
			if ($query->rowCount() !== 1) {
				throw new \Exception('Delete menu-item faalt: $query->rowCount() =' . $query->rowCount());
			}
			$db->commit();
		} catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

}

?>