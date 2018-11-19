<?php


namespace CsrDelft\model\entity\bibliotheek;


use CsrDelft\model\bibliotheek\BoekModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

class BoekExemplaar extends PersistentEntity {

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

	public $uitgeleend_uid;

	public $toegevoegd;

	public $status = 'beschikbaar';

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
		} elseif ($this->isBiebBoek() AND LoginModel::mag('P_BIEB_MOD')) {
			return true;
		}
		return false;
	}

	public function magBewerken() : bool {
		return $this->isEigenaar();
	}
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @return Boek
	 */
	public function getBoek() {
		return BoekModel::instance()->get($this->boek_id);
	}

	public function magBekijken() {
		return LoginModel::mag('P_BIEB_READ') OR $this->magBewerken();
	}

	public function isBeschikbaar() {
		return $this->getStatus() == 'beschikbaar';
	}

	public function kanLenen(string $uid) {
		return $this->eigenaar_uid != $uid && $this->isBeschikbaar();
	}

	public function isUitgeleend() {
		return $this->status == 'uitgeleend';
	}

	public function isTeruggegeven() {
		return $this->status == 'teruggegeven';
	}

	public function isVermist() {
		return $this->status == 'vermist';
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
		'status' => [T::Enumeration, false, BoekExemplaarStatus::class],
		'leningen' => [T::Integer, false]
	];


	/**
	 * @var string[]
	 */
	protected static $primary_key = ['id'];
	protected static $table_name = 'biebexemplaar';
}