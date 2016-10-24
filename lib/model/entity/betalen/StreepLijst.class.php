<?php

/**
 * StreepLijst.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class StreepLijst extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $streeplijst_id;
	/**
	 * Titel
	 * @var string
	 */
	public $titel;
	/**
	 * Rechten benodigd voor strepen
	 * @var string
	 */
	public $streep_rechten;
	/**
	 * Gemaakt door lidnummer
	 * Foreign key
	 * @var string
	 */
	public $gemaakt_uid;
	/**
	 * ProductPrijsLijst ID
	 * Foreign key
	 * @var int
	 */
	public $prijslijst_id;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'streeplijst_id' => array(T::Integer, false, 'auto_increment'),
		'titel'			 => array(T::String),
		'streep_rechten' => array(T::String),
		'gemaakt_uid'	 => array(T::UID),
		'prijslijst_id'	 => array(T::Integer, true)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('streeplijst_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'streeplijsten';

}
