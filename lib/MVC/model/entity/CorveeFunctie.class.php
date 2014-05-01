<?php

/**
 * CorveeFunctie.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * 
 * Een CorveeFunctie instantie beschrijft een functie die een lid kan uitvoeren als taak en of hiervoor een kwalificatie nodig is.
 * Zo ja, dan moet een lid op moment van toewijzen van de taak over deze kwalificatie beschikken (lid.id moet voorkomen in tabel crv_kwalificaties).
 * 
 * Bijvoorbeeld:
 *  - Tafelpraeses
 *  - Kwalikok (kwalificatie benodigd!)
 *  - Afwasser
 *  - Keuken/Afzuigkap/Frituur schoonmaker
 *  - Klusser
 * 
 * Standaard punten wordt standaard overgenomen, maar kan worden overschreven per corveetaak.
 * 
 * 
 * Zie ook CorveeKwalificatie.class.php en CorveeTaak.class.php
 * 
 */
class CorveeFunctie extends PersistentEntity {

	/**
	 * Primary key
	 * @var int 
	 */
	public $functie_id;
	/**
	 * Naam
	 * @var string
	 */
	public $naam;
	/**
	 * Afkorting
	 * @var string 
	 */
	public $afkorting;
	/**
	 * E-mailbericht
	 * @var string
	 */
	public $email_bericht;
	/**
	 * Standaard aantal corveepunten
	 * @var int 
	 */
	public $standaard_punten;
	/**
	 * Is een kwalificatie benodigd
	 * @var boolean 
	 */
	public $kwalificatie_benodigd;
	/**
	 * Kwalificaties
	 * @var CorveeKwalificatie[]
	 */
	private $kwalificaties;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'functie_id' => array('int', 11, false, null, 'auto_increment'),
		'naam' => array('string', 255),
		'afkorting' => array('string', 3),
		'email_bericht' => array('text'),
		'standaard_punten' => array('int', 11),
		'kwalificatie_benodigd' => array('boolean')
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('functie_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'crv_functies';

	/**
	 * Lazy loading by foreign key.
	 * 
	 * @return CorveeKwalificatie[]
	 */
	public function getKwalificaties() {
		if (!isset($this->kwalificaties)) {
			$this->setKwalificaties(KwalificatiesModel::instance()->getKwalificatiesVoorFunctie($this->functie_id));
		}
		return $this->kwalificaties;
	}

	public function hasKwalificaties() {
		return sizeof($this->getKwalificaties()) > 0;
	}

	public function setKwalificaties(array $kwalificaties) {
		$this->kwalificaties = $kwalificaties;
	}

}
