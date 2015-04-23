<?php

/**
 * KeywordTag.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class KeywordTag {

	/**
	 * @see PersistentEntity Unique Universal Identifier
	 * @var string 
	 */
	public $uuid;
	/**
	 * Single keyword
	 * @var string
	 */
	public $keyword;
	/**
	 * Getagged door
	 * @var string
	 */
	public $door;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'uuid'		 => array(T::String),
		'keyword'	 => array(T::String),
		'door'		 => array(T::UID)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('uuid', 'keyword');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'keyword_tags';

}
