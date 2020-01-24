<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\repository\ProfielRepository;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;


/**
 * AbstractGroepLid.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een lid van een groep.
 *
 */
abstract class AbstractGroepLid extends PersistentEntity {

	/**
	 * Shared primary key
	 * Foreign key
	 * @var int
	 */
	public $groep_id;
	/**
	 * Lidnummer
	 * Shared primary key
	 * Foreign key
	 * @var string
	 */
	public $uid;
	/**
	 * CommissieFunctie of opmerking bij lidmaatschap
	 * @var CommissieFunctie
	 */
	public $opmerking;
	/**
	 * @var GroepKeuzeSelectie[]
	 */
	public $opmerking2;
	/**
	 * Datum en tijd van aanmelden
	 * @var string
	 */
	public $lid_sinds;
	/**
	 * Lidnummer van aanmelder
	 * @var string
	 */
	public $door_uid;

	public function getLink() {
		return ProfielRepository::getLink($this->uid);
	}

	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = [
		'groep_id' => [T::Integer],
		'uid' => [T::UID],
		'opmerking' => [T::String, true],
		'opmerking2' => [T::JSON, true, [GroepKeuzeSelectie::class]],
		'lid_sinds' => [T::DateTime],
		'door_uid' => [T::UID]
	];

	protected static $computed_attributes = [
		'link' => [T::String],
	];
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = ['groep_id', 'uid'];

}
