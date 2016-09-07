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
		'ketzer_id'		 => array(T::Integer),
		'keuze_soort'	 => array(T::Enumeration, false, 'KetzerSelectorSoort')
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'ketzer_selectors';

	/**
	 * Lazy loading by foreign key.
	 * 
	 * @return KetzerOptie[]
	 */
	public function getOpties() {
		return KetzerOptiesModel::instance()->getOptiesVoorSelect($this);
	}

}
