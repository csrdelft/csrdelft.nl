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
	public $lid_id;
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
		'lid_id' => 'varchar(4) NOT NULL',
		'functie_id' => 'int(11) NOT NULL',
		'wanneer_toegewezen' => 'datetime NOT NULL'
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('lid_id', 'functie_id');
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
		echo 'getCorveeFunctie()';

		if (!isset($this->corvee_functie)) {
			$this->setCorveeFunctie(FunctiesModel::instance()->find('functie_id = ?', array($this->functie_id)));
		}
		return $this->corvee_functie;
	}

	public function setCorveeFunctie(CorveeFunctie $functie) {
		echo 'setCorveeFunctie()';

		$this->corvee_functie = $functie;
	}

}
