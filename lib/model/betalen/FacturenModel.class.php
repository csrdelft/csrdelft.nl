<?php

/**
 * FacturenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class FacturenModel extends PersistenceModel {

	const ORM = 'Factuur';
	const DIR = 'betalen/';

	protected static $instance;

	public function newFactuur($klant_id, $titel, $toelichting = null, $ontvangst_iban = null, $termijnen = 1, $doodlijn_moment = null, $voldaan_moment = null) {
		$factuur = new Factuur();
		$factuur->klant_id = $klant_id;
		$factuur->titel = $titel;
		$factuur->toelichting = $toelichting;
		$factuur->ontvangst_iban = $ontvangst_iban;
		$factuur->termijnen = $termijnen;
		$factuur->doodlijn_moment = $doodlijn_moment;
		$factuur->voldaan_moment = $voldaan_moment;
		return $factuur;
	}

	/**
	 * Set primary key.
	 *
	 * @param PersistentEntity $factuur
	 * @return int factuur_id
	 */
	public function create(PersistentEntity $factuur) {
		$factuur->factuur_id = (int) parent::create($factuur);
		return $factuur->factuur_id;
	}

}
