<?php

/**
 * MaaltijdBeoordeling.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * 
 * Een MaaltijdBeoordeling instantie beschrijft een beoordeling door een lid van een maaltijd.
 * Op basis hiervan worden statistieken bepaald waarbij de beoordelingen genormaliseerd worden.
 * 
 */
class MaaltijdBeoordeling extends PersistentEntity {

	/**
	 * Primary key
	 * @var int 
	 */
	public $maaltijd_id;
	/**
	 * Lidnummer
	 * @var string
	 */
	public $uid;
	/**
	 * Kwantiteit beoordeling
	 * @var float
	 */
	public $kwantiteit;
	/**
	 * Kwaliteit beoordeling
	 * @var float
	 */
	public $kwaliteit;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'maaltijd_id'	 => array(T::UnsignedInteger),
		'uid'			 => array(T::UID),
		'kwantiteit'	 => array(T::Float, true),
		'kwaliteit'		 => array(T::Float, true)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('maaltijd_id', 'uid');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'mlt_beoordelingen';

}
