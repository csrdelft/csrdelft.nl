<?php

/**
 * ForumDraadGelezen.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een ForumDraad kan worden gelezen door een lid op een bepaald moment.
 * 
 */
class ForumDraadGelezen extends PersistentEntity {

	/**
	 * Shared primary key
	 * @var int
	 */
	public $draad_id;
	/**
	 * Shared primary key
	 * @var string
	 */
	public $lid_id;
	/**
	 * Datum en tijd van laatst gelezen
	 * @var string
	 */
	public $datum_tijd;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'draad_id' => 'int(11) NOT NULL',
		'lid_id' => 'varchar(4) NOT NULL',
		'datum_tijd' => 'datetime NOT NULL'
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('draad_id', 'lid_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'forum_draden_gelezen';

}
