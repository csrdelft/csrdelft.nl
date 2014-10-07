<?php

/**
 * ForumDraadVolgen.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een ForumDraad kan worden gevolgd door een lid.
 * 
 */
class ForumDraadVolgen extends PersistentEntity {

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
	protected static $persistent_attributes = array(
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
	protected static $table_name = 'forum_draden_volgen';

}
