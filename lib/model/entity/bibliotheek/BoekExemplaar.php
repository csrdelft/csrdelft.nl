<?php


namespace CsrDelft\model\entity\bibliotheek;


use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

class BoekExemplaar extends PersistentEntity {

	protected static $table_name = 'biebexemplaar';

	/**
	 * @var int id
	 */
	public $id;
	/**
	 * @var int id
	 */
	public $boek_id;
	/**
	 * @var string uid
	 */
	public $eigenaar_uid;
	/**
	 * @var string uid
	 */
	public $opmerking;

	public $uigeleend_uid;

	public $toegevoegd;

	public $status;

	public $uitleendatum;
	/**
	 * @var int leningen
	 */
	public $leningen;


	public function isBiebBoek() : bool {
		return $this->eigenaar_uid == 'x222';
	}

	public function isEigenaar() : bool {
		if ($this->eigenaar_uid == LoginModel::getUid()) {
			return true;
		} elseif ($this->isBiebBoek() AND LoginModel::mag('R_BASF')) {
			return true;
		}
		return false;
	}

	public function getStatus() {
		// @TODO geef juiste status
		return "beschikbaar";
	}

	/**
	 * @var array
	 */
	protected static $persistent_attributes = [
		'id' => [T::Integer, false, "auto_increment"],
		'boek_id' => [T::Integer, false],
		'eigenaar_uid' => [T::String, false],
		'opmerking' => [T::Text, false],
		'uitgeleend_uid' => [T::String, true],
		'toegevoegd' => [T::DateTime, false],
		'uitleendatum' => [T::DateTime, true],
		'status' => [T::Enumeration, false],
		'leningen' => [T::Integer, false]
	];

	/**
	 * @var string[]
	 */
	protected static $primary_key = ['id'];
}