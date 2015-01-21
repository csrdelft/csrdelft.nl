<?php

require_once 'model/entity/groepen/OnderverenigingStatus.enum.php';

/**
 * Ondervereniging.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Ondervereniging extends Groep {

	/**
	 * (Adspirant-)Ondervereniging
	 * @var OnderverenigingStatus
	 */
	public $status;
	/**
	 * Veranderingen van status
	 * @var string
	 */
	public $status_historie;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'status'			 => array(T::Enumeration, false, 'OnderverenigingStatus'),
		'status_historie'	 => array(T::Text)
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

}
