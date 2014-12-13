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
	 * Get menu for viewing.
	 * Use 2 levels of caching.
	 * 
	 * @param string $naam
	 * @return MenuItem root
	 */
	public function getMenu($naam) {
		if (empty($naam)) {
			return null;
		}
		$key = $naam . '-menu';
		if ($this->isCached($key, true)) {
			$loaded = $this->isCached($key, false); // is the tree root present in runtime cache?
			$result = $this->getCached($key, true); // this only puts the tree root in runtime cache
			if (!$loaded) {
				$this->cacheResult($this->getList($result), false); // put tree children in runtime cache as well
			}
			return $result;
		}
		// not cached
		$root = $this->getMenuRoot($naam);
		if ($root) {
			$this->getTree($root);
		} else {
			// niet bestaand menu?
			$root = $this->newMenuItem(0);
			$root->tekst = $naam;
			if ($naam == LoginModel::getUid()) {
				// maak favorieten menu 
				$root->link = '/menubeheer/beheer/' . $naam;
			}
			$root->link = '';
			$this->create($root);
		}
		$this->setCache($key, $root, true);
		return $root;
	}

	/**
	 * Build tree structure.
	 * 
	 * @param MenuItem $root
	 * @return MenuItem $root
	 */
	public function getTree(MenuItem $root) {
		foreach ($root->getChildren() as $child) {
			$this->getTree($child);
		}
		return $root;
	}

	/**
	 * Flatten tree structure.
	 * 
	 * @param MenuItem $root
	 * @return MenuItem[]
	 */
	public function getList(MenuItem $root) {
		$list = $root->getChildren();
		foreach ($list as $child) {
			$list = array_merge($list, $this->getList($child));
		}
		return $list;
	}

	public function getMenuRoot($naam) {
		return $this->find('parent_id = ? AND tekst = ? ', array(0, $naam), null, null, 1)->fetch(); // is cached at higher level
	}

	public function getChildren(MenuItem $parent) {
		return $this->prefetch('parent_id = ?', array($parent->item_id), 'prioriteit ASC, tekst ASC'); // cache for getParent()
	}

	/**
	 * Lijst van alle menu roots om te beheren.
	 * 
	 * @return MenuItem[]
	 */
	public function getMenuBeheerLijst() {
		if (LoginModel::mag('P_ADMIN')) {
			return $this->find('parent_id = ?', array(0), 'tekst DESC');
		} else {
			return false;
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
		$this->flushCache(true);
	}

	public function update(PersistentEntity $entity) {
		$rowcount = parent::update($entity);
		$this->flushCache(true);
		return $rowcount;
	}

	public function removeMenuItem(MenuItem $item) {
		$db = Database::instance();
		try {
			$db->beginTransaction();
			// give new parent to otherwise future orphans
			$update = array('parent_id' => $item->parent_id);
			$where = 'parent_id = :oldid';
			$rowcount = Database::sqlUpdate($this->orm->getTableName(), $update, $where, array(':oldid' => $item->item_id));
			$this->delete($item);
			$db->commit();
			$this->flushCache(true);
			return $rowcount;
		} catch (Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

}
