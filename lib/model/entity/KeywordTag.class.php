<?php

/**
 * KeywordTag.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class KeywordTag extends PersistentEntity {

	/**
	 * @see PersistentEntity Unique Universal Identifier
	 * @var string 
	 */
	public $refuuid;
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
	 * Wanneer gemaakt
	 * @var datetime
	 */
	public $wanneer;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'refuuid'	 => array(T::String),
		'keyword'	 => array(T::String),
		'door'		 => array(T::UID),
		'wanneer'	 => array(T::DateTime)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('refuuid', 'keyword');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'keyword_tags';

}
