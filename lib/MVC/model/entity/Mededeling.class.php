<?php

require_once 'MVC/model/entity/PersistentEntity.class.php';

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
	public $zichtbaar_van = null;
	public $zichtbaar_tot = null;
	public $prioriteit = 0;
	public $afbeelding_url = null;
	private static $_persist = array(
		'id' => 'int(11) NOT NULL AUTO_INCREMENT',
		'type' => 'varchar(255) NOT NULL',
		'tekst' => 'text NOT NULL',
		'zichtbaar_voor' => 'varchar(255) NOT NULL',
		'zichtbaar_van' => 'datetime NOT NULL',
		'zichtbaar_tot' => 'datetime',
		'prioriteit' => 'int(11) NOT NULL',
		'afbeelding_url' => 'text');
	private static $_primary_key = array('id');

	public static function getPersistentFields() {
		return self::$_persist;
	}

	public static function getPrimaryKey() {
		return self::$_primary_key;
	}

}

?>