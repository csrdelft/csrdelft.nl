<?php

namespace CsrDelft\model;

use CsrDelft\model\documenten\DocumentCategorieModel;
use CsrDelft\model\entity\documenten\DocumentCategorie;
use CsrDelft\model\entity\MenuItem;
use CsrDelft\model\forum\ForumModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\CachedPersistenceModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\Entity\PersistentEntity;

/**
 * MenuModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class MenuModel extends CachedPersistenceModel {

	const ORM = MenuItem::class;

	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'volgorde ASC, tekst ASC';

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
			$root = $this->getCached($key, true); // this only puts the tree root in runtime cache
			if (!$loaded) {
				$this->cacheResult($this->flattenMenu($root), false); // put tree children in runtime cache as well
			}
			return $root;
		}
		// not cached
		$root = $this->getMenuRoot($naam);
		if ($root) {
			$this->getExtendedTree($root);
		} else {
			// niet bestaand menu?
			$root = $this->nieuw(0);
			$root->tekst = $naam;
			if ($naam == LoginModel::getUid()) {
				// maak favorieten menu
				$root->link = '/menubeheer/beheer/' . $naam;
			} else {
				$root->link = '';
			}
			$this->create($root);
		}
		$this->cache($root); // cache for getParent()
		$this->setCache($key, $root, true);
		return $root;
	}

	/**
	 * Voeg forum-categorien, forum-delen, documenten-categorien en verticalen toe aan menu.
	 * Deze komen in memcache terecht.
	 *
	 * @param MenuItem $parent
	 * @return MenuItem $parent
	 */
	public function getExtendedTree(MenuItem $parent) {
		foreach ($parent->getChildren() as $child) {
			$this->getExtendedTree($child);
		}
		// append additional children
		switch ($parent->tekst) {

			case 'Forum':
				foreach (ForumModel::instance()->prefetch() as $categorie) {
					$item = $this->nieuw($parent->item_id);
					$item->item_id = -$categorie->categorie_id; // nodig voor getParent()
					$item->rechten_bekijken = $categorie->rechten_lezen;
					$item->link = '/forum#' . $categorie->categorie_id;
					$item->tekst = $categorie->titel;
					$parent->children[] = $item;
					$this->cache($item);

					foreach ($categorie->getForumDelen() as $deel) {
						$subitem = $this->nieuw($item->item_id);
						$subitem->rechten_bekijken = $deel->rechten_lezen;
						$subitem->link = '/forum/deel/' . $deel->forum_id;
						$subitem->tekst = $deel->titel;
						$item->children[] = $subitem;
					}
				}
				foreach (MenuModel::instance()->getMenu('remotefora')->getChildren() as $remotecat) {
					$parent->children[] = $remotecat;
				}
				break;

			case 'Documenten':
				$overig = false;
				foreach (DocumentCategorieModel::instance()->find() as $categorie) {
					/** @var DocumentCategorie $categorie */
					$item = $this->nieuw($parent->item_id);
					$item->rechten_bekijken = $categorie->leesrechten;
					$item->link = '/documenten/categorie/' . $categorie->id;
					$item->tekst = $categorie->naam;
					if (!$overig AND $item->tekst == 'Overig') {
						$overig = $item;
					} else {
						$parent->children[] = $item;
					}
				}
				if ($overig) {
					$parent->children[] = $overig;
				}
				break;
		}
		return $parent;
	}

	/**
	 * Build tree structure.
	 *
	 * @param MenuItem $parent
	 * @return MenuItem $parent
	 */
	public function getTree(MenuItem $parent) {
		foreach ($parent->getChildren() as $child) {
			$this->getTree($child);
		}
		return $parent;
	}

	/**
	 * Flatten tree structure.
	 *
	 * @param MenuItem $root
	 * @return MenuItem[]
	 */
	public function flattenMenu(MenuItem $root) {
		$list = $root->getChildren();
		foreach ($list as $child) {
			$list = array_merge($list, $this->flattenMenu($child));
		}
		return $list;
	}

	/**
	 * @param string $naam
	 *
	 * @return bool|MenuItem
	 */
	public function getMenuRoot($naam) {
		$root = $this->find('parent_id = ? AND tekst = ? ', array(0, $naam), null, null, 1)->fetch();
		if ($root) {
			return $this->cache($root);
		}
		return false;
	}

	/**
	 * @param MenuItem $parent
	 *
	 * @return MenuItem[]
	 */
	public function getChildren(MenuItem $parent) {
		return $this->prefetch('parent_id = ?', array($parent->item_id)); // cache for getParent()
	}

	/**
	 * Lijst van alle menu roots om te beheren.
	 *
	 * @return MenuItem[]|false
	 */
	public function getMenuBeheerLijst() {
		if (LoginModel::mag('P_ADMIN')) {
			return $this->find('parent_id = ?', array(0));
		} else {
			return false;
		}
	}

	/**
	 * @param MenuItem $item
	 *
	 * @return MenuItem
	 */
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
	 * @return MenuItem|false
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

	/**
	 * @param int $parent_id
	 *
	 * @return MenuItem
	 */
	public function nieuw($parent_id) {
		$item = new MenuItem();
		$item->parent_id = $parent_id;
		$item->volgorde = 0;
		$item->rechten_bekijken = LoginModel::getUid();
		$item->zichtbaar = true;
		return $item;
	}

	/**
	 * @param PersistentEntity|MenuItem $entity
	 *
	 * @return void
	 */
	public function create(PersistentEntity $entity) {
		$entity->item_id = (int)parent::create($entity);
		$this->flushCache(true);
	}

	/**
	 * @param PersistentEntity|MenuItem $entity
	 * @return int
	 */
	public function update(PersistentEntity $entity) {
		$this->flushCache(true);
		return parent::update($entity);
	}

	/**
	 * @param MenuItem $item
	 *
	 * @return int
	 */
	public function removeMenuItem(MenuItem $item) {
		return Database::transaction(function () use ($item) {
			// give new parent to otherwise future orphans
			$update = array('parent_id' => $item->parent_id);
			$where = 'parent_id = :oldid';
			$rowCount = Database::instance()->sqlUpdate($this->getTableName(), $update, $where, array(':oldid' => $item->item_id));
			$this->delete($item);
			$this->flushCache(true);
			return $rowCount;
		});
	}
}
