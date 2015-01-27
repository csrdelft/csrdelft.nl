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

}
