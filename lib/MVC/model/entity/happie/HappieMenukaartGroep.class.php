<?php

require_once 'MVC/model/entity/happie/HappieGang.enum.php';

/**
 * MenukaartGroep.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Groepering van menuitems op menukaart.
 * 
 */
class HappieMenukaartGroep extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $groep_id;
	/**
	 * Titel
	 * @var string
	 */
	public $titel;
	/**
	 * Gang van gerechtengroep
	 * @var HappieGang
	 */
	public $gang;
	/**
	 * Items
	 * @var HappieMenukaartItem[]
	 */
	protected $items;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'groep_id'	 => array(T::Integer, false, 'auto_increment'),
		'titel'		 => array(T::String),
		'gang'		 => array(T::Enumeration, false, 'HappieGang')
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('groep_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'happie_menu_groep';

	public function getItems() {
		if (!isset($this->items)) {
			$this->setItems(HappieMenukaartItemsModel::instance()->getGroepItems($this));
		}
		return $this->items;
	}

	public function hasItems() {
		$this->getItems();
		return !empty($this->items);
	}

	public function setItems(array $items) {
		$this->items = $items;
	}

}
