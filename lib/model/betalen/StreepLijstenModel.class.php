<?php

/**
 * StreepLijstenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class StreepLijstenModel extends PersistenceModel {

	const ORM = 'StreepLijst';

	protected static $instance;

	public function maakStreepLijst($titel, $streep_rechten, $prijslijst_id = null) {
		$streeplijst = new StreepLijst();
		$streeplijst->titel = $titel;
		$streeplijst->streep_rechten = $streep_rechten;
		$streeplijst->gemaakt_uid = LoginModel::getUid();
		$streeplijst->prijslijst_id = $prijslijst_id;
		$streeplijst->streeplijst_id = (int) $this->create($streeplijst);
		return $streeplijst;
	}

}
