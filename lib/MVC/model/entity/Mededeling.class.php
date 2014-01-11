<?php

/**
 * Mededeling.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Mededeling2 extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $id = null;
	/**
	 * Bestuur, commissie, lid
	 * @var string
	 */
	public $type = '';
	/**
	 * Inhoud
	 * @var string 
	 */
	public $tekst = '';
	/**
	 * Iedereen, leden, oud-leden, niemand, prullenbak
	 * @var string
	 */
	public $zichtbaar_voor = 'niemand';
	/**
	 * Vanaf dit moment zichtbaar
	 * @var DateTime
	 */
	public $zichtbaar_vanaf = '0000-00-00 00:00:00';
	/**
	 * Tot dit moment zichtbaar
	 * @var DateTime
	 */
	public $zichtbaar_tot = null;
	/**
	 * Volgorde van weergave
	 * @var int
	 */
	public $prioriteit = 0;
	/**
	 * Url naar afbeelding van 200x200
	 * @var string
	 */
	public $afbeelding_url = null;
	/**
	 * Database table fields
	 * @var array
	 */
	public static $persistent_fields = array(
		'id' => 'int(11) NOT NULL AUTO_INCREMENT',
		'type' => 'varchar(255) NOT NULL',
		'tekst' => 'text NOT NULL',
		'zichtbaar_voor' => 'varchar(255) NOT NULL',
		'zichtbaar_vanaf' => 'datetime NOT NULL',
		'zichtbaar_tot' => 'datetime',
		'prioriteit' => 'int(11) NOT NULL',
		'afbeelding_url' => 'text'
	);
	/**
	 * Database primary key
	 * @var array
	 */
	public static $primary_key = array('id');
	/**
	 * Database table name
	 * @var string
	 */
	public static $table_name = 'mededelingen';

}

?>