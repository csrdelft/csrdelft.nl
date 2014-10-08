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
	public $item_id;
	/**
	 * Dit menu-item is een sub-item van
	 * @var int
	 */
	public $parent_id;
	/**
	 * Volgorde van weergave
	 * @var int
	 */
	public $prioriteit;
	/**
	 * Link tekst
	 * @var string
	 */
	public $tekst;
	/**
	 * Link url
	 * @var string
	 */
	public $link;
	/**
	 * LoginModel::mag
	 * @var string
	 */
	public $rechten_bekijken;
	/**
	 * Zichtbaar of verborgen
	 * @var boolean
	 */
	public $zichtbaar;
	/**
	 * State of menu GUI
	 * @var boolean
	 */
	public $active;
	/**
	 * De sub-items van dit menu-item
	 * @var array
	 */
	public $children;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'item_id'			 => array(T::Integer, false, 'auto_increment'),
		'parent_id'			 => array(T::Integer, false),
		'prioriteit'		 => array(T::Integer, false),
		'tekst'				 => array(T::String),
		'link'				 => array(T::String),
		'rechten_bekijken'	 => array(T::String, false),
		'zichtbaar'			 => array(T::Boolean, false)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('item_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'menus';

	public function getChildren() {
		if (!isset($this->children)) {
			$this->children = MenuModel::instance()->getChildren($this);
		}
		return $this->children;
	}

	public function hasChildren() {
		return !empty($this->getChildren());
	}

	/**
	 * Do not store parent as well as children:
	 * bi-directional not possible for serialization.
	 * 
	 * @return MenuItem
	 */
	public function getParent() {
		return MenuModel::instance()->getParent($this);
	}

	/**
	 * Bepaald of het gevraagde menu-item een
	 * sub-item is van dit menu-item.
	 * 
	 * @param MenuItem $item
	 * @return boolean
	 */
	public function isParentOf(MenuItem $item) {
		if ($this->item_id === $item->parent_id) {
			return false;
		}
		foreach ($this->getChildren() as $child) {
			if ($child->isParentOf($item)) {
				return true;
			}
		}
		return false;
	}

	public function magBekijken() {
		return LoginModel::mag($this->rechten_bekijken);
	}

	public function magBeheren() {
		return $this->rechten_bekijken == LoginModel::getUid() OR LoginModel::mag('P_ADMIN');
	}

}
