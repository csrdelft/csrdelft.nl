<?php

/**
 * ChangeLogEntry.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class ChangeLogEntry extends PersistentEntity {

	/**
	 * The moment it changed
	 * @var string
	 */
	public $moment;
	/**
	 * The thing that changed
	 * @var string
	 */
	public $subject;
	/**
	 * The property that changed
	 * @var string
	 */
	public $property;
	/**
	 * The value before
	 * @var string
	 */
	public $old_value;
	/**
	 * The value after
	 * @var string
	 */
	public $new_value;
	/**
	 * Lidnummer of who did it
	 * @var string
	 */
	public $uid;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'moment'	 => array(T::DateTime),
		'subject'	 => array(T::String),
		'property'	 => array(T::String),
		'old_value'	 => array(T::Text, true),
		'new_value'	 => array(T::Text, true),
		'uid'		 => array(T::UID)
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'changelog';

}
