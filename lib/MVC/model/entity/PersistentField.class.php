<?php

/**
 * PersistentField.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class PersistentField {

	/**
	 * Name
	 * @var string
	 */
	public $field;
	/**
	 * Type definition
	 * @var int
	 */
	public $type;
	/**
	 * Allowed to be NULL: 'YES' or 'NO'
	 * @var string
	 */
	public $null;
	/**
	 * Key type: 'PRI' or 'MUL' or empty
	 * @var string
	 */
	public $key;
	/**
	 * Default value
	 * @var string
	 */
	public $default;
	/**
	 * Additional properties like 'auto_increment'
	 * @var string
	 */
	public $extra;

}
