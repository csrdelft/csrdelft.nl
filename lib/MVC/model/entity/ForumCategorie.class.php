<?php

/**
 * ForumCategorie.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een forum categorie bevat deelfora.
 * 
 */
class ForumCategorie extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $categorie_id = null;
	/**
	 * Titel
	 * @var string
	 */
	public $titel = '';
	/**
	 * Omschrijving
	 * @var string
	 */
	public $omschrijving = '';
	/**
	 * Rechten benodigd voor bekijken
	 * @var string
	 */
	public $zichtbaar_voor = 'P_FORUM_READ';
	/**
	 * Weergave volgorde
	 * @var int
	 */
	public $prioriteit = 0;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'categorie_id' => 'int(11) NOT NULL AUTO_INCREMENT',
		'titel' => 'varchar(255) NOT NULL',
		'omschrijving' => 'text NOT NULL',
		'zichtbaar_voor' => 'varchar(25) NOT NULL',
		'prioriteit' => 'int(11) NOT NULL'
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('categorie_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'forum_categorien';

}
