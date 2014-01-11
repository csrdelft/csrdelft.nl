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
	 * 
	 * @var int
	 */
	protected $id = null;
	/**
	 * Bestuur, commissie, lid
	 * 
	 * @var string
	 */
	protected $type = '';
	protected $tekst = '';
	/**
	 * Iedereen, leden, oud-leden, niemand, prullenbak
	 * 
	 * @var string
	 */
	protected $zichtbaar_voor = 'niemand';
	protected $zichtbaar_vanaf = '0000-00-00 00:00:00';
	protected $zichtbaar_tot = null;
	protected $prioriteit = 0;
	protected $afbeelding_url = null;
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