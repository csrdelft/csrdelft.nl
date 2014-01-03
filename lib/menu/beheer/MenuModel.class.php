<?php
require_once 'menu/beheer/MenuItem.class.php';

/**
 * MenuModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class MenuModel {

	public static function getAlleMenus() {
		return self::loadMenuItems(null, array(), true);
	}
	
	public static function getMenuItem($mid) {
		$menus = self::loadMenuItems('menu_id = ?', array($mid));
		return reset($menus);
	}
	
	public static function getMenuTree($menu) {
		if ($menu === null) {
			$items = array();
		}
		else {
			$items = self::loadMenuItems('menu = ?', array($menu));
		}
		$root = new MenuItem();
		$root->setTekst($menu);
		$root->setMenu($menu);
		self::addChildren($root, $items);
		foreach ($items as $item) {
			setMelding('Parent '. $item->getParentId() .' bestaat niet: '. $item->getTekst() .' ('. $item->getMenuId() .')', -1);
			$root->children[] = $item;
		}
		return $root;
	}
	
	private static function addChildren($parent, &$children) {
		foreach ($children as $i => $item) {
			if ($parent->getMenuId() === $item->getParentId()) { // this is the correct parent
				$parent->children[] = $item;
				unset($children[$i]); // only one parent
				self::addChildren($item, $children); // add children of children
			}
		}
	}
	
	private static function loadMenuItems($where=null, $values=array(), $menusOnly=false) {
		if ($menusOnly) {
			$sql = 'SELECT DISTINCT menu';
		}
		else {
			$sql = 'SELECT menu_id, parent_id, prioriteit, tekst, link, permission, zichtbaar, menu';
		}
		$sql.= ' FROM menu';
		if ($where !== null) {
			$sql.= ' WHERE '. $where;
		}
		$sql.= ' ORDER BY parent_id ASC, prioriteit ASC';
		$db = \CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		$result = $query->fetchAll(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, 'MenuItem');
		return $result;
	}
	
	public static function newMenuItem($pid, $prio, $text, $link, $perm, $show, $menu) {
		$sql = 'INSERT INTO crv_functies';
		$sql.= ' (functie_id, naam, afkorting, email_bericht, standaard_punten, kwalificatie_benodigd)';
		$sql.= ' VALUES (?, ?, ?, ?, ?, ?, ?)';
		$values = array(null, $pid, $prio, $text, $link, $perm, $show, $menu);
		$db = \CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		if ($query->rowCount() !== 1) {
			throw new \Exception('New functie faalt: $query->rowCount() ='. $query->rowCount());
		}
		return new CorveeFunctie(intval($db->lastInsertId()), $naam, $afk, $email, $punten, $kwali);
	}
	
	public static function updateMenuItem(MenuItem $item) {
		$sql = 'UPDATE menu';
		$sql.= ' SET parent_id=?, prioriteit=?, tekst=?, link=?, permission=?, zichtbaar=?';
		$sql.= ' WHERE menu_id=?';
		$values = array(
			$item->getParentId(),
			$item->getPrioriteit(),
			$item->getTekst(),
			$item->getLink(),
			$item->getPermission(),
			$item->getIsZichtbaar(),
			$item->getMenuId()
		);
		$db = \CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
	}
	
	public static function deleteMenuItem(MenuItem $item) {
		$db = \CsrPdo::instance();
		try {
			$db->beginTransaction();
			foreach ($item->children as $child) { // give new parent to otherwise future orphans
				$child->setParentId($item->getParentId());
				self::updateMenuItem($child);
			}
			$sql = 'DELETE FROM menu';
			$sql.= ' WHERE menu_id = ?';
			$values = array($item->getMenuId());
			$query = $db->prepare($sql, $values);
			$query->execute($values);
			if ($query->rowCount() !== 1) {
				throw new \Exception('Delete menu-item faalt: $query->rowCount() ='. $query->rowCount());
			}
			$db->commit();
		}
		catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}
}

?>