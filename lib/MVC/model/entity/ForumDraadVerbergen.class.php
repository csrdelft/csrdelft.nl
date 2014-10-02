<?php

/**
 * ForumDraadVerbergen.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een ForumDraad kan worden verborgen door een lid.
 * 
 */
class ForumDraadVerbergen extends PersistentEntity {

	/**
	 * Shared primary key
	 * @var int
	 */
	public $draad_id;
	/**
	 * Shared primary key
	 * @var string
	 */
	public $uid;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'draad_id'	 => array(T::Integer),
		'uid'		 => array(T::UID)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('draad_id', 'uid');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'forum_draden_verbergen';

}
