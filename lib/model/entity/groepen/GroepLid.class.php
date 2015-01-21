<?php

require_once 'model/entity/groepen/CommissieFunctie.enum.php';

/**
 * GroepLid.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een lid van een groep.
 * 
 */
class GroepLid extends PersistentEntity {

	/**
	 * Class extends Groep
	 * @var class
	 */
	public $groep_class;
	/**
	 * Primary key of Groep
	 * @var int
	 */
	public $groep_id;
	/**
	 * Lidnummer
	 * @var string
	 */
	public $uid;
	/**
	 * CommissieFunctie of opmerking bij lidmaatschap
	 * @var CommissieFunctie
	 */
	public $opmerking;
	/**
	 * Datum en tijd van aanmelden
	 * @var string
	 */
	public $lid_sinds;
	/**
	 * Datum en tijd van o.t.
	 * @var string
	 */
	public $lid_tot;
	/**
	 * o.t. / h.t. / f.t.
	 * @var GroepStatus
	 */
	public $status;
	/**
	 * Volgorde van weergave
	 * @var string
	 */
	public $volgorde;
	/**
	 * Lidnummer van aanmelder
	 * @var string
	 */
	public $door_uid;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'groep_class'	 => array(T::String),
		'groep_id'		 => array(T::Integer),
		'uid'			 => array(T::UID),
		'opmerking'		 => array(T::String, true),
		'lid_sinds'		 => array(T::DateTime),
		'lid_tot'		 => array(T::DateTime, true),
		'status'		 => array(T::Enumeration, false, 'GroepStatus'),
		'volgorde'		 => array(T::Integer),
		'door_uid'		 => array(T::UID)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('groep_class', 'groep_id', 'uid');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'groep_leden';

}
