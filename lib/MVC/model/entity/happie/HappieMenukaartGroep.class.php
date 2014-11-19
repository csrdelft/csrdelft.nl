<?php

require_once 'MVC/model/entity/happie/HappieGang.enum.php';
require_once 'MVC/model/happie/MenukaartItemsModel.class.php';

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
	 * Naam
	 * @var string
	 */
	public $naam;
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
	 * Beschikbaar voor x aantal bestellingen
	 * @var int
	 */
	public $aantal_beschikbaar;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'groep_id'			 => array(T::Integer, false, 'auto_increment'),
		'naam'				 => array(T::String),
		'gang'				 => array(T::Enumeration, false, 'HappieGang'),
		'aantal_beschikbaar' => array(T::Integer)
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

	public function jsonSerialize() {
		$array = parent::jsonSerialize();
		$array['gang'] = ucfirst($this->gang);
		if ($this->gang !== HappieGang::Drank) {
			$array['gang'] .= 'gerecht';
		}
		return $array;
	}

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
