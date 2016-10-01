<?php

/**
 * KlantenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class KlantenModel extends PersistenceModel {

	const ORM = 'Klant';
	const DIR = 'betalen/';

	protected static $instance;

	public static function getKlant($uid) {
		return static::instance()->find('uid = ?', array($uid), null, null, 1)->fetch();
	}

	public function newKlant($naam, $uid = null, $civi_saldo = null, $soccie_saldo = null, $maalcie_saldo = null) {
		$klant = new Klant();
		$klant->naam = $naam;
		$klant->uid = $uid;
		$klant->civi_saldo = $civi_saldo;
		$klant->soccie_saldo = $soccie_saldo;
		$klant->maalcie_saldo = $maalcie_saldo;
		return $klant;
	}

	/**
	 * Set primary key.
	 *
	 * @param PersistentEntity $klant
	 * @return int klant_id
	 */
	public function create(PersistentEntity $klant) {
		$klant->klant_id = (int) parent::create($klant);
		return $klant->klant_id;
	}

}
