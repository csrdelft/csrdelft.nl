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

	/**
	 * Extend the persistent attributes.
	 */
	public static function __constructStatic() {
		parent::__constructStatic();
		self::$persistent_attributes = parent::$persistent_attributes + self::$persistent_attributes;
	}

	public function getUrl() {
		return '/groepen/woonoorden/' . $this->id . '/';
	}

	/**
	 * Has permission for action?
	 * 
	 * @param AccessAction $action
	 * @return boolean
	 */
	public function mag($action) {
		if ($action === A::Beheren) {
			if ($this->status === GroepStatus::HT) {
				// Huidige bewoners mogen beheren
				if ($this->getLid(LoginModel::getUid())) {
					return true;
				}
			} elseif ($this->status === GroepStatus::OT) {
				// Huidige bewoners mogen beheren
				if (LoginModel::mag('woonoord:' . $this->familie)) {
					return true;
				}
			}
		}
		return parent::mag($action);
	}

}
