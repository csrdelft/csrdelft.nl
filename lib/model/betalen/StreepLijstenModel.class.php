<?php

/**
 * StreepLijstenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class StreepLijstenModel extends PersistenceModel {

	const ORM = 'StreepLijst';
	const DIR = 'betalen/';

	protected static $instance;

	public function newStreepLijst($titel, $streep_rechten, $prijslijst_id = null) {
		$streeplijst = new StreepLijst();
		$streeplijst->titel = $titel;
		$streeplijst->streep_rechten = $streep_rechten;
		$streeplijst->gemaakt_uid = LoginModel::getUid();
		$streeplijst->prijslijst_id = $prijslijst_id;
		return $streeplijst;
	}

	/**
	 * Set primary key.
	 *
	 * @param PersistentEntity $streeplijst
	 * @return int streeplijst_id
	 */
	public function create(PersistentEntity $streeplijst) {
		$streeplijst->streeplijst_id = (int) parent::create($streeplijst);
		return $streeplijst->streeplijst_id;
	}

}
