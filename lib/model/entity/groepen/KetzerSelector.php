<?php

require_once 'model/entity/groepen/KetzerSelectorSoort.enum.php';

/**
 * KetzerSelector.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een soort selector (AND/XOR) heeft keuzemogelijkheden.
 * 
 */
class KetzerSelector extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $select_id;
	/**
	 * Selector van deze ketzer
	 * @var int
	 */
	public $ketzer_id;
	/**
	 * Single selection (KeuzeRondje) / Multiple selection (Vinkje)
	 * @var KetzerSelectorSoort
	 */
	public $keuze_soort;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'select_id'		 => array(T::UnsignedInteger, false, 'auto_increment'),
		'ketzer_id'		 => array(T::UnsignedInteger),
		'keuze_soort'	 => array(T::Enumeration, false, 'KetzerSelectorSoort')
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'ketzer_selectors';
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('select_id');

	/**
	 * Lazy loading by foreign key.
	 * 
	 * @return KetzerOptie[]
	 */
	public function getOpties() {
		return KetzerOptiesModel::instance()->getOptiesVoorSelect($this);
	}

}
