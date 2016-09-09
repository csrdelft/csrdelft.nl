<?php

/**
 * KetzerOptie.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een keuzemogelijkheid van een ketzer kan gekozen worden door een groeplid.
 * 
 */
class KetzerOptie extends PersistentEntity {

	/**
	 * Foreign key
	 * @var int
	 */
	public $select_id;
	/**
	 * Keuzewaarde
	 * @var string
	 */
	public $waarde;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'select_id'	 => array(T::Integer),
		'waarde'	 => array(T::String)
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'ketzer_opties';

	/**
	 * Lazy loading by foreign key.
	 * 
	 * @return KetzerKeuze[]
	 */
	public function getKeuzes() {
		return KetzerKeuzesModel::instance()->getKeuzesVoorOptie($this);
	}

}
