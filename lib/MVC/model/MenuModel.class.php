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
		CsrMemcache::instance()->delete($this->getRoot($item)->tekst . '-menu');
		$this->flushCache();
	}

	/**
	 * Get menu for viewing.
	 * Use 2 levels of caching.
	 * 
	 * @param string $naam
	 * @return MenuItem root
	 */
	public function getMenu($naam) {
		// Only unserialize once
		if ($this->isCached($naam)) {
			return $this->getCached($naam);
		}
		$cached = CsrMemcache::instance()->get($naam . '-menu');
		if ($cached !== false) {
			$root = unserialize($cached);
		} else {
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
				$this->create($root);
			}
			CsrMemcache::instance()->set($naam . '-menu', serialize($root));
		}
		$this->setCache($naam, $root);
		return $root;
	}

	/**
	 * Build tree structure.
	 * 
	 * @param MenuItem $root
	 * @return MenuItem root
	 */
	public function getTree(MenuItem $root) {
		foreach ($root->getChildren() as $child) {
			$this->getTree($child);
		}
		return $root;
	}

	public function getMenuRoot($naam) {
		$result = $this->find('parent_id = ? AND tekst = ? ', array(0, $naam), null, null, 1);
		if (count($result) === 1) {
			return $result[0];
		} else {
			return false;
		}
	}

	public function getChildren(MenuItem $parent) {
		var_dump($parent);

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
	 * Lijst van alle menu roots om te beheren.
	 * 
	 * @return MenuItem[]
	 */
	public function getMenuBeheerLijst() {
		if (LoginModel::mag('P_ADMIN')) {
			return $this->find('parent_id = ?', array(0), 'tekst DESC');
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
