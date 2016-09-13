<?php

/**
 * KlantenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class KlantenModel extends PersistenceModel {

	const ORM = 'Klant';

	protected static $instance;

	public function maakKlant($naam, $uid = null, $civi_saldo = null, $soccie_saldo = null, $maalcie_saldo = null) {
		$klant = new Klant();
		$klant->naam = $naam;
		$klant->uid = $uid;
		$klant->civi_saldo = $civi_saldo;
		$klant->soccie_saldo = $soccie_saldo;
		$klant->maalcie_saldo = $maalcie_saldo;
		$klant->klant_id = (int) $this->create($klant);
		return $klant;
	}

}
