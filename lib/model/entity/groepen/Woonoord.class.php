<?php

require_once 'model/entity/groepen/HuisStatus.enum.php';

/**
 * Woonoord.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een woonoord is waar C.S.R.-ers bij elkaar wonen.
 * 
 */
class Woonoord extends AbstractGroep {

	const leden = 'BewonersModel';

	/**
	 * Woonoord / Huis
	 * @var HuisStatus
	 */
	public $soort;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'soort' => array(T::Enumeration, false, 'HuisStatus')
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'woonoorden';

	public function getUrl() {
		return '/groepen/woonoorden/' . $this->id . '/';
	}

	/**
	 * Has permission for action?
	 * 
	 * @param AccessAction $action
	 * @param string $soort
	 * @return boolean
	 */
	public function mag($action, $soort = null) {
		switch ($action) {

			case A::Beheren:
			case A::Wijzigen:
				// Huidige bewoners mogen beheren
				if (LoginModel::mag('woonoord:' . $this->familie)) {
					// HuisStatus wijzigen wordt geblokkeerd in GroepForm->validate()
					return true;
				}
				break;
		}
		return parent::mag($action);
	}

}
