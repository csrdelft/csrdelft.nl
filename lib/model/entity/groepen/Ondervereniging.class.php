<?php

require_once 'model/entity/groepen/OnderverenigingStatus.enum.php';

/**
 * Ondervereniging.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Ondervereniging extends AbstractGroep {

	const leden = 'OnderverenigingsLedenModel';

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

	/**
	 * Extend the persistent attributes.
	 */
	public static function __constructStatic() {
		parent::__constructStatic();
		self::$persistent_attributes = parent::$persistent_attributes + self::$persistent_attributes;
	}

	public function getUrl() {
		return '/groepen/onderverenigingen/' . $this->id . '/';
	}

}
