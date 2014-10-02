<?php

/**
 * SocCieKlant.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Het SocCie systeem bevat niet alleen leden maar ook externe klanten.
 * Daarom hebben SocCie klanten een eigen unieke id binnen het SocCie systeem
 * en zijn deze gekoppeld aan een lid via lidnummer.
 */
class SocCieKlant extends PersistentEntity {

	/**
	 * Unique identifier of customer in the SocCie system
	 * @var int
	 */
	public $socCieId;
	/**
	 * Lidnummer
	 * @var string
	 */
	public $stekUID;
	/**
	 * Saldo value
	 * @var int
	 */
	public $saldo;
	/**
	 * Text value of customer name
	 * @var string
	 */
	public $naam;
	/**
	 * Customer deleted state
	 * @var boolean
	 */
	public $deleted;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'socCieId'	 => array(T::Integer),
		'stekUID'	 => array(T::UID),
		'saldo'		 => array(T::Integer),
		'naam'		 => array(T::Text),
		'deleted'	 => array(T::Boolean, false)
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'socCieKlanten';
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('socCieId');

	public function getSaldoFloat() {
		return ((float) $this->saldo) / 100;
	}

}
