<?php

/**
 * CorveeKwalificatie.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * 
 * Een CorveeKwalificatie instantie geeft aan dat een lid gekwalificeerd is voor een functie en sinds wanneer.
 * Dit is benodigd voor sommige CorveeFuncties zoals kwalikok.
 * 
 * 
 * Zie ook CorveeFunctie.class.php
 * 
 */
class CorveeKwalificatie extends PersistentEntity {

	/**
	 * Lid id
	 * @var string
	 */
	public $uid;
	/**
	 * Functie id
	 * @var int
	 */
	public $functie_id;
	/**
	 * Datum + tijd
	 * @var string
	 */
	public $wanneer_toegewezen;
	/**
	 * Functie instantie
	 * @var CorveeFunctie
	 */
	private $corvee_functie;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'uid' => array(T::UID),
		'functie_id' => array(T::Integer),
		'wanneer_toegewezen' => array(T::DateTime)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('uid', 'functie_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'crv_kwalificaties';

	/**
	 * Lazy loading by foreign key.
	 * 
	 * @return CorveeFunctie
	 */
	public function getCorveeFunctie() {
		if (!isset($this->corvee_functie)) {
			$this->setCorveeFunctie(FunctiesModel::instance()->getFunctie($this->functie_id));
		}
		return $this->corvee_functie;
	}

	public function setCorveeFunctie(CorveeFunctie $functie) {
		$this->corvee_functie = $functie;
	}

}
