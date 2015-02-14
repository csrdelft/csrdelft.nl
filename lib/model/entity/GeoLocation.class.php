<?php

/**
 * GeoLocation.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GeoLocation extends PersistentEntity {

	/**
	 * Primary key
	 * @var string
	 */
	public $uid;
	/**
	 * Primary key
	 * @var DateTime
	 */
	public $moment;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'uid'		 => array(T::UID),
		'moment'	 => array(T::DateTime),
		'position'	 => array(T::Text)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('uid', 'moment');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'geolocations';

}
