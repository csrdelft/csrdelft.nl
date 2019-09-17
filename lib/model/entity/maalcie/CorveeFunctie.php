<?php

namespace CsrDelft\model\entity\maalcie;

use CsrDelft\model\maalcie\KwalificatiesModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

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
    # ID om functie van kwalikok op te halen, wijzigen als ID van Kwalikok wijzigt
    const KWALIKOK_FUNCTIE_ID = 7;

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
	 * Geeft deze functie speciale rechten
	 * om maaltijden te mogen sluiten
	 * @var boolean
	 */
	public $maaltijden_sluiten;
	/**
	 * Kwalificaties
	 * @var CorveeKwalificatie[]
	 */
	private $kwalificaties;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'functie_id' => array(T::Integer, false, 'auto_increment'),
		'naam' => array(T::String),
		'afkorting' => array(T::String),
		'email_bericht' => array(T::Text),
		'standaard_punten' => array(T::Integer),
		'kwalificatie_benodigd' => array(T::Boolean),
		'maaltijden_sluiten' => array(T::Boolean)
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

	private function setKwalificaties(array $kwalificaties) {
		$this->kwalificaties = $kwalificaties;
	}

}
