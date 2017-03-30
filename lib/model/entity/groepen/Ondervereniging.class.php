<?php

use CsrDelft\Orm\Entity\T;

require_once 'model/entity/groepen/OnderverenigingStatus.enum.php';

/**
 * Ondervereniging.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Ondervereniging extends AbstractGroep {

	const LEDEN = 'OnderverenigingsLedenModel';

	/**
	 * (Adspirant-)Ondervereniging
	 * @var OnderverenigingStatus
	 */
	public $soort;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'soort' => array(T::Enumeration, false, 'OnderverenigingStatus'),
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'onderverenigingen';

	public function getUrl() {
		return '/groepen/onderverenigingen/' . $this->id . '/';
	}

}
