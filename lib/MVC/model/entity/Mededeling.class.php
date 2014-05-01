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
		'$mededeling_id' => array('int', 11, false, null, 'auto_increment'),
		'type' => array('string', 255),
		'tekst' => array('text'),
		'zichtbaar_voor' => array('string', 255),
		'zichtbaar_vanaf' => array('datetime'),
		'zichtbaar_tot' => array('datetime'),
		'prioriteit' => array('int', 11),
		'afbeelding_url' => array('text')
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
