<?php

require_once 'model/entity/groepen/Ketzer.class.php';

/**
 * Werkgroep.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Werkgroep extends Ketzer {

	const leden = 'WerkgroepDeelnemersModel';

	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'werkgroepen';

	public function getUrl() {
		return '/groepen/werkgroepen/' . $this->id . '/';
	}

	/**
	 * Rechten voor de gehele klasse of soort groep?
	 * 
	 * @param string $action
	 * @return boolean
	 */
	public static function magAlgemeen($action) {
		if ($action === A::Aanmaken AND ! LoginModel::mag('P_LEDEN_MOD')) {
			return false;
		}
		return parent::magAlgemeen($action);
	}

}
