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
	 * Primary key
	 * @var int
	 */
	public $select_id;
	/**
	 * Vinkje (AND) / KeuzeRondje (XOR)
	 * @see KetzerSelectorSoort
	 * @var string
	 */
	public $keuze_soort;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'ketzer_id'		 => array(T::Integer),
		'select_id'		 => array(T::Integer),
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
	protected static $primary_key = array('ketzer_id', 'select_id');

	/**
	 * Lazy loading by foreign key.
	 * 
	 * @return KetzerOptie[]
	 */
	public function getKetzerOpties() {
		if (!isset($this->ketzer_selectors)) {
			$this->setKetzerOpties(KetzerOptiesModel::instance()->getOptiesVoorSelect($this));
		}
		return $this->ketzer_selectors;
	}

	public function hasKetzerOpties() {
		return count($this->getKetzerOpties()) > 0;
	}

	private function setKetzerOpties(array $opties) {
		$this->ketzer_selectors = $opties;
	}

}
