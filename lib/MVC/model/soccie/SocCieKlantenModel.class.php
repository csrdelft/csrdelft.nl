<?php

/**
 * SocCieKlantenModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Het SocCie systeem bevat niet alleen leden maar ook externe klanten.
 */
class SocCieKlantenModel extends PersistenceModel {

	const orm = 'SocCieKlant';

	protected static $instance;

	protected function __construct() {
		parent::__construct('soccie/');
	}

	/**
	 * Haalt het saldo op voor de klant met opgegeven lidnummer.
	 * 
	 * @param string $uid
	 * @return float
	 */
	public function getSaldoVoorLid($uid) {
		$klant = $this->find('stekUID = ?', array($uid), null, null, 1)->fetch();
		if (!$klant) {
			return 0;
		}
		return $klant->getSaldoFloat();
	}

}
