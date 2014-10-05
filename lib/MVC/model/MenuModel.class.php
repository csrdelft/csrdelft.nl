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
	 * Menus by name
	 * @var array
	 */
	private $menus;

	protected function __construct() {
		parent::__construct();
		$this->loadMenus(array('main', LoginModel::getUid()));
	}

	public function getMenuRoot($naam) {
		return $this->find('parent_id = ? AND tekst = ? ', array(0, $naam), null, null, 1)->fetch();
	}

	/**
	 * Lijst van alle menu roots om te beheren.
	 * 
	 * @return PDOStatement
	 */
	public function getBeheerMenusVoorLid() {
		if (LoginModel::mag('P_ADMIN')) {
			return $this->find('parent_id = ?', array(0), 'tekst ASC');
		} else {
			return array();
		}
	}

	/**
	 * Haalt alle menu-items op (die zichtbaar zijn voor de gebruiker).
	 * Filtert de menu-items met de permissies van het ingelogede lid.
	 * 
	 * @param string $naam
	 * @return MenuItem[]
	 */
	public function getMenuTree($naam) {
		// haal uit cache?
		if (!isset($this->menus[$naam])) {
			$this->loadMenus(array($naam));
		}
		// niet bestaand menu?
		if (!isset($this->menus[$naam])) {
			$item = $this->newMenuItem(0);
			$item->tekst = $naam;
			if ($naam == LoginModel::getUid()) {
				$item->link = '/menubeheer/beheer/' . $naam;
			}
			$item->item_id = (int) $this->create($item);
			$this->menus[$naam] = $item;
		}
		return $this->menus[$naam];
	}

	private function loadMenus(array $menus) {
		// haal alle menu items op en groepeer op parent id
		$items = group_by('parent_id', $this->find(null, array(), 'prioriteit ASC'));
		// totaal geen menu roots?
		if (isset($items[0])) {
			// voor alle menus de tree opbouwen
			foreach ($items[0] as $i => $root) {
				// is dit een van de gevraagde menus?
				if (in_array($root->tekst, $menus)) {
					$this->setChildren($root, $items);
					// zet in cache
					$this->menus[$root->tekst] = $root;
				} else {
					// directe kinderen uit lijst verwijderen
					unset($items[$root->item_id]);
				}
				// opruimen uit root lijst
				unset($items[0][$i]);
			}
		}
	}

	private function setChildren(MenuItem $parent, &$items) {
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
			// is dit menu item active?
			$child->active = startsWith(REQUEST_URI, $child->link);
			// parent is active als child active is
			$parent->active |= $child->active;
			// mag gebruiker menu item zien?
			if ($child->magBekijken()) {
				$this->setChildren($child, $items);
			} else {
				unset($parent->children[$i]);
			}
		}
	}

	public function getMenuItem($id) {
		return $this->retrieveByPrimaryKey(array($id));
	}

	public function newMenuItem($parent_id) {
		$item = new MenuItem();
		$item->parent_id = $parent_id;
		$item->prioriteit = 0;
		$item->rechten_bekijken = LoginModel::getUid();
		$item->zichtbaar = true;
		return $item;
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
			return $rowcount;
		} catch (Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

}
