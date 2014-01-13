<?php

/**
 * MenuItem.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een menu-item instantie beschrijft een menu onderdeel van een menu-boom
 * en heeft daarom een parent.
 * 
 */
class MenuItem extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $item_id = null;
	/**
	 * Dit menu-item is een sub-item van
	 * @var int
	 */
	public $parent_id = null;
	/**
	 * Volgorde van weergave
	 * @var int
	 */
	public $prioriteit = 0;
	/**
	 * Link tekst
	 * @var string
	 */
	public $tekst = '';
	/**
	 * Link url
	 * @var string
	 */
	public $link = '';
	/**
	 * LoginLid::hasPermission
	 * @var string
	 */
	public $permission = '';
	/**
	 * Zichtbaar of verborgen
	 * @var boolean
	 */
	public $zichtbaar = false;
	/**
	 * Unieke naam per menu
	 * @var string
	 */
	public $menu_naam = 'main';
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'item_id' => 'int(11) NOT NULL AUTO_INCREMENT',
		'parent_id' => 'int(11) NOT NULL',
		'prioriteit' => 'int(11) NOT NULL',
		'tekst' => 'varchar(50) NOT NULL',
		'link' => 'varchar(255) NOT NULL',
		'permission' => 'varchar(255) NOT NULL',
		'zichtbaar' => 'tinyint(1) NOT NULL',
		'menu_naam' => 'varchar(255) NOT NULL'
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('menu_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'menus';
	/**
	 * De sub-items van dit menu-item
	 * @var array
	 */
	public $children = array();

	/**
	 * Bepaald of het gevraagde menu-item een
	 * sub-item is van dit menu-item.
	 * 
	 * @param MenuItem $item
	 * @return boolean
	 */
	public function isParentOf(MenuItem $item) {
		if ($this->item_id === $item->parent_id) {
			return true;
		}
		foreach ($this->children as $child) {
			if ($child->isParentOf($item)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Doorloopt een lijst met menu-items en
	 * voegt recursief de kinderen toe.
	 * 
	 * @param MenuItem[] $items
	 */
	public function addChildren(array &$items) {
		foreach ($items as $i => $child) {
			if ($this->item_id === $child->parent_id) { // this is the correct parent
				$this->children[] = $child;
				unset($items[$i]); // only one parent
				$child->addChildren($items); // add children of children
			}
		}
	}

}
