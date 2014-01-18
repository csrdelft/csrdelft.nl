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
	 * @var int
	 */
	public $mededeling_id;
	/**
	 * Bestuur, commissie, lid
	 * @var string
	 */
	public $type;
	/**
	 * Textuele inhoud met eventueel UBB
	 * @var string 
	 */
	public $tekst;
	/**
	 * Iedereen, leden, oud-leden, niemand, prullenbak
	 * @var string
	 */
	public $zichtbaar_voor;
	/**
	 * Vanaf dit moment zichtbaar
	 * @var DateTime
	 */
	public $zichtbaar_vanaf;
	/**
	 * Tot dit moment zichtbaar
	 * @var DateTime
	 */
	public $zichtbaar_tot;
	/**
	 * Volgorde van weergave
	 * @var int
	 */
	public $prioriteit = 0;
	/**
	 * Url naar afbeelding van 200x200
	 * @var string
	 */
	public $afbeelding_url;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'$mededeling_id' => 'int(11) NOT NULL AUTO_INCREMENT',
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
	protected static $primary_key = array('$mededeling_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'mededelingen';

}
