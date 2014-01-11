<?php

/**
 * Mededeling.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Mededeling extends PersistentEntity {

	/**
	 * Primary key
	 * 
	 * @var int
	 */
	public $id = null;

	/**
	 * Bestuur, commissie, lid
	 * 
	 * @var string
	 */
	public $type = '';
	public $tekst = '';

	/**
	 * Iedereen, leden, oud-leden, niemand, prullenbak
	 * 
	 * @var string
	 */
	public $zichtbaar_voor = 'niemand';
	public $zichtbaar_vanaf = '0000-00-00 00:00:00';
	public $zichtbaar_tot = null;
	public $prioriteit = 0;
	public $afbeelding_url = null;

	/**
	 * Database
	 */
	public static $table_name = 'mededelingen';
	public static $persistent_fields = array(
		'id' => 'int(11) NOT NULL AUTO_INCREMENT',
		'type' => 'varchar(255) NOT NULL',
		'tekst' => 'text NOT NULL',
		'zichtbaar_voor' => 'varchar(255) NOT NULL',
		'zichtbaar_vanaf' => 'datetime NOT NULL',
		'zichtbaar_tot' => 'datetime',
		'prioriteit' => 'int(11) NOT NULL',
		'afbeelding_url' => 'text');
	public static $primary_key = array('id');

}

?>