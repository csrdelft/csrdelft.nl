<?php

namespace CsrDelft\model\entity\maalcie;

use CsrDelft\model\maalcie\FunctiesModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

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
	 * Lidnummer
	 * Shared primary key
	 * Foreign key
	 * @var string
	 */
	public $uid;
	/**
	 * Shared primary key
	 * Foreign key
	 * @var int
	 */
	public $functie_id;
	/**
	 * Datum en tijd
	 * @var string
	 */
	public $wanneer_toegewezen;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
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
		return FunctiesModel::get($this->functie_id);
	}

}
