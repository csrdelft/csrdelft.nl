<?php

/**
 * MenuModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class MenuModel extends CachedPersistenceModel {

	const orm = 'MenuItem';

	protected static $instance;

	/**
	 * Remove cached menu from memcache and clear runtime cache.
	 * 
	 * @param MenuItem $item
	 */
	public function clearCache(MenuItem $item) {
		CsrMemcache::instance()->delete($this->getRoot($item)->tekst);
		$this->flushCache();
	}

	/**
	 * Get menu for viewing.
	 * 
	 * @param string $naam
	 * @return MenuItem root
	 */
	public function getMenu($naam) {
		$root = CsrMemcache::instance()->get($naam);
		if ($root !== false) {
			return unserialize($root);
		} else {
			$root = $this->getMenuRoot($naam);
			CsrMemcache::instance()->set($naam, serialize($this->getTree($root)));
			return $root;
		}
	}

	/**
	 * Build tree structure.
	 * 
	 * @param MenuItem $root
	 * @return MenuItem root
	 */
	public function getTree(MenuItem $root) {
		foreach ($root->getChildren() as $child) {
			if ($child->zichtbaar) {
				$this->getTree($child);
			}
		}
		return $root;
	}

	public function getMenuRoot($naam) {
		return $this->find('parent_id = ? AND tekst = ? ', array(0, $naam), null, null, 1)->fetch();
	}

	public function getChildren(MenuItem $parent) {
		$children = array();
		foreach ($this->find('parent_id = ?', array($parent->item_id), 'prioriteit ASC') as $child) {
			$children[] = $child;
			// cache for getParent()
			$key = $this->cacheKey($child->getValues(true));
			$this->setCache($key, $child);
		}
		return $children;
	}

	/**
	 * Get menu for beheer.
	 * 
	 * @param string $naam
	 * @return MenuItem root
	 */
	public function getMenuBeheer($naam) {
		$root = $this->getMenuRoot($naam);
		if ($root->magBeheren()) {
			return $this->getTreeBeheer($root);
		}
		return false;
	}

	/**
	 * Build tree structure for beheer.
	 * 
	 * @param MenuItem $root
	 * @return MenuItem
	 */
	public function getTreeBeheer(MenuItem $root) {
		foreach ($root->getChildren() as $child) {
			$this->getTree($child);
		}
		return $root;
	}

	/**
	 * Lijst van alle menu roots om te beheren.
	 * 
	 * @return MenuItem[]
	 */
	public function getMenuRootsBeheer() {
		if (LoginModel::mag('P_ADMIN')) {
			return $this->find('parent_id = ?', array(0), 'tekst DESC')->fetchAll();
		} else {
			return array();
		}
	}

	public function getRoot(MenuItem $item) {
		if ($item->parent_id === 0) {
			return $item;
		}
		return $this->getRoot($item->getParent());
	}

	/**
	 * Get menu item by id (cached).
	 * 
	 * @param int $id
	 * @return MenuItem
	 */
	public function getMenuItem($id) {
		return $this->retrieveByPrimaryKey(array($id));
	}

	/**
	 * Get the parent of a menu item (cached).
	 * 
	 * @param MenuItem $item
	 * @return MenuItem
	 */
	public function getParent(MenuItem $item) {
		return $this->getMenuItem($item->parent_id);
	}

	public function newMenuItem($parent_id) {
		$item = new MenuItem();
		$item->parent_id = $parent_id;
		$item->prioriteit = 0;
		$item->rechten_bekijken = LoginModel::getUid();
		$item->zichtbaar = true;
		return $item;
	}

	public function create(PersistentEntity $entity) {
		$entity->item_id = (int) parent::create($entity);
		$this->clearCache($entity);
	}

	public function update(PersistentEntity $entity) {
		$rowcount = parent::update($entity);
		$this->clearCache($entity);
		return $rowcount;
	}

	public function removeMenuItem(MenuItem $item) {
		$db = Database::instance();
		try {
			$db->beginTransaction();
			// give new parent to otherwise future orphans
			$update = array('parent_id' => $item->parent_id);
			$where = 'parent_id = :oldid';
			$orm = self::orm;
			$rowcount = Database::sqlUpdate($orm::getTableName(), $update, $where, array(':oldid' => $item->item_id));
			$this->delete($item);
			$db->commit();
			$this->clearCache($item);
			return $rowcount;
		} catch (Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

}
