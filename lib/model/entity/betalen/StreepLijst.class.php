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
	 * ProductPrijsLijst ID
	 * Foreign key
	 * @var int
	 */
	public $prijslijst_id;
	/**
	 * Beheer-rechten
	 * @var string
	 */
	public $beheer_rechten;
	/**
	 * Gemaakt door lidnummer
	 * Foreign key
	 * @var string
	 */
	public $gemaakt_uid;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'streeplijst_id' => array(T::Integer, false, 'auto_increment'),
		'titel'			 => array(T::String),
		'prijslijst_id'	 => array(T::Integer, true),
		'beheer_rechten' => array(T::String),
		'gemaakt_uid'	 => array(T::UID)
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
