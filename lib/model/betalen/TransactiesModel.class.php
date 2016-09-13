<?php

/**
 * TransactiesModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class TransactiesModel extends PersistenceModel {

	const ORM = 'Transactie';

	protected static $instance;

	public function maakTransactie($factuur_id, $betalingsmethode, $bedrag, $ontvangst_iban = null, $betaler_iban = null, $geslaagd = null, $link_transactie_id = null) {
		$transactie = new Transactie();
		$transactie->factuur_id = $factuur_id;
		$transactie->moment = getDateTime();
		$transactie->betalingsmethode = $betalingsmethode;
		$transactie->bedrag = $bedrag;
		$transactie->ontvangst_iban = $ontvangst_iban;
		$transactie->betaler_iban = $betaler_iban;
		$transactie->geslaagd = $geslaagd;
		$transactie->link_transactie_id = $link_transactie_id;
		$transactie->transactie_id = (int) $this->create($transactie);
		return $transactie;
	}

}
