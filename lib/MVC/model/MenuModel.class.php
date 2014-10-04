<?php

/**
 * MenuModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class MenuModel extends PersistenceModel {

	const orm = 'MenuItem';

	protected static $instance;
	/**
	 * Menus[$beheer] by name
	 * @var array
	 */
	private $menus = array();

	/**
	 * Array van alle menu roots.
	 * 
	 * @return PDOStatement
	 */
	public function getAlleMenuRoots() {
		return $this->find('parent_id = ?', array(0), 'prioriteit ASC');
	}

	/**
	 * Haalt alle menu-items op (die zichtbaar zijn voor de gebruiker).
	 * Filtert de menu-items met de permissies van het ingelogede lid.
	 * 
	 * @param string $naam
	 * @param boolean $beheer
	 * @return MenuItem[]
	 */
	public function getMenuTree($naam, $beheer = false) {
		// haal uit cache?
		if (isset($naam, $this->menus[$beheer])) {
			return $this->menus[$beheer][$naam];
		}
		// in beheer modus ook onzichtbare items tonen
		if ($beheer) {
			$where = null;
		} else {
			$where = 'zichtbaar = TRUE';
		}
		// haal alle menu items op en groepeer op parent id
		$items = group_by('parent_id', $this->find($where, array(), 'prioriteit ASC'));
		// totaal geen menu roots?
		if (isset($items[0])) {
			// voor alle menus de tree opbouwen
			foreach ($items[0] as $i => $root) {
				$this->setChildren($root, $items, $beheer);
				// opruimen uit root lijst
				unset($items[0][$i]);
				// zet in cache
				$this->menus[$beheer][$root->tekst] = $root;
			}
		}
		// niet bestaand menu?
		if (!isset($this->menus[$beheer][$naam])) {
			$item = $this->newMenuItem(0);
			$item->tekst = $naam;
			return $item;
		}
		return $this->menus[$beheer][$naam];
	}

	private function setChildren(MenuItem $parent, &$items, $beheer) {
		// geen items met dit id = geen kinderen
		if (!array_key_exists($parent->item_id, $items)) {
			return;
		}
		// zet kinderen
		$parent->children = $items[$parent->item_id];
		// kind kan maar 1 parent hebben
		unset($items[$parent->item_id]);
		// voor elk kind ook kinderen zetten
		foreach ($parent->children as $i => $child) {
			// is dit menu item current?
			$child->current = startsWith(REQUEST_URI, $child->link);
			// parent is current als child current is
			$parent->current |= $child->current;
			// mag gebruiker menu item zien?
			if ($beheer OR $child->magBekijken()) {
				$this->setChildren($child, $items, $beheer);
			} else {
				unset($parent->children[$i]);
			}
		}
	}

	public function getChildren(MenuItem $parent, $beheer = false) {
		$where = 'parent_id = ?' . ($beheer ? '' : ' AND zichtbaar = true');
		$parent->children = $this->find($where, array($parent->item_id), 'prioriteit ASC')->fetchAll();
		$child_current = false;
		foreach ($parent->children as $i => $child) {
			if (!$beheer AND ! $child->magBekijken()) {
				unset($parent->children[$i]);
				continue;
			}
			$child->current = startsWith(REQUEST_URI, $child->link);
			$child_current |= $child->current;
			$this->getChildren($child, $beheer);
		}
		$parent->current |= $child_current; // make parent of current child also current
	}

	public function getMenuItem($id) {
		return $this->retrieveByPrimaryKey(array($id));
	}

	public function newMenuItem($parent_id) {
		$item = new MenuItem();
		$item->parent_id = $parent_id;
		$item->prioriteit = 0;
		$item->link = '/';
		$item->rechten_bekijken = 'P_PUBLIC';
		$item->zichtbaar = false;
		return $item;
	}

	public function removeMenuItem(MenuItem $item) {
		$db = Database::instance();
		try {
			$db->beginTransaction();
			// give new parent to otherwise future orphans
			$properties = array('parent_id' => $item->parent_id);
			$orm = self::orm;
			$count = Database::sqlUpdate($orm::getTableName(), $properties, 'parent_id = :oldid', array(':oldid' => $item->item_id));
			$this->delete($item);
			$db->commit();
			SimpleHTML::setMelding($count . ' menu-items nieuwe parent gegeven.', 2);
		} catch (Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

}
