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
	 * LoginLid::hasPermission
	 * @var string
	 */
	public $rechten_bekijken;
	/**
	 * Zichtbaar of verborgen
	 * @var boolean
	 */
	public $zichtbaar;
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
		'rechten_bekijken' => 'varchar(255) NOT NULL',
		'zichtbaar' => 'tinyint(1) NOT NULL'
	);
	/**
	 * Form input fields
	 * @var array
	 */
	protected static $input_fields = array(
		'parent_id' => array('RequiredIntField', 'Item id van element 1 niveau hoger', 0),
		'prioriteit' => array('IntField', 'Sortering van items'),
		'tekst' => array('TextField', 'Korte aanduiding', 50),
		'link' => array('TextField', 'URL als er op het menu item geklikt wordt', 255),
		'rechten_bekijken' => array('TextField', 'Wie mag dit menu item zien', 255)
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

}
