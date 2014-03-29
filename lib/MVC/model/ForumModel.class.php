<?php

/**
 * ForumModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class ForumModel extends PaginationModel {

	protected static $instance;
	protected static $orm = 'ForumItem';

	/**
	 * Lijst van alle menus.
	 * 
	 * @return array
	 */
	public function getAlleForums() {
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
	 * @return ForumItem[]
	 */
	public function getForumTree($menu_naam, $admin = false) {
		// get root
		$where = 'parent_id = 0 AND tekst = ?';
		$params = array($menu_naam);
		$root = $this->find($where, $params, 'parent_id ASC, prioriteit ASC');
		if (sizeof($root) <= 0) {
			$item = $this->newForumItem(0);
			$item->tekst = $menu_naam;
			return $item;
		}
		$this->getChildren($root[0], $admin);
		return $root[0];
	}

	public function getChildren(ForumItem $item, $admin = false) {
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

	public function getForumItem($id) {
		$item = $this->retrieveByPrimaryKey(array($id));
		if (!$item) {
			throw new Exception('Forum-item ' . $id . ' bestaat niet');
		}
		return $item;
	}

	public function newForumItem($parent_id) {
		$item = new ForumItem();
		$item->parent_id = intval($parent_id);
		$item->prioriteit = 0;
		$item->link = '/';
		$item->rechten_bekijken = 'P_NOBODY';
		$item->zichtbaar = true;
		return $item;
	}

	public function removeForumItem($item_id) {
		$db = Database::instance();
		try {
			$db->beginTransaction();
			$item = $this->getForumItem($item_id);
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
