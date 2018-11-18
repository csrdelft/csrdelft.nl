<?php


namespace CsrDelft\model\entity\bibliotheek;


use CsrDelft\model\bibliotheek\BoekModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

class BoekRecensie extends PersistentEntity {

	public $id;
	public $boek_id;
	public $schrijver_uid;
	public $beschrijving;
	public $toegevoegd;
	public $bewerkdatum;

	/*
	 * @param 	$uid lidnummer of null
	 * @return	bool
	 * 		een beschrijving mag door schrijver van beschrijving en door admins bewerkt worden.
	 */

	public function isSchrijver($uid = null) {
		if (!LoginModel::mag('P_LOGGED_IN')) {
			return false;
		}
		if ($uid === null) {
			$uid = LoginModel::getUid();
		}
		return $this->schrijver_uid == $uid;
	}


	/**
	 * controleert rechten voor bewerkactie
	 *
	 * @return bool
	 *        een beschrijving mag door schrijver van beschrijving en door admins bewerkt worden.
	 */
	public function magVerwijderen() {
		return $this->isSchrijver();
	}

	/**
	 * @var string[]
	 */
	protected static $primary_key = ['id'];

	public function magBewerken() {
		return $this->isSchrijver();
	}

	/**
	 * @return Boek
	 */
	public function getBoek() {
		return BoekModel::instance()->get($this->boek_id);
	}

	/**
	 * @var array
	 */
	protected static $persistent_attributes = [
		'id' => [T::Integer, false, "auto_increment"],
		'boek_id' => [T::Integer, false],
		'schrijver_uid' => [T::String, false],
		'beschrijving' => [T::Text, false],
		'toegevoegd' => [T::DateTime, false],
		'bewerkdatum' => [T::DateTime, false]
	];

	protected static $table_name = 'biebbeschrijving';
}