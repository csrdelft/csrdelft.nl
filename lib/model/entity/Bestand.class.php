<?php

require_once 'model/entity/Map.class.php';

/**
 * Bestand.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Bestand extends PersistentEntity {

	/**
	 * Bestandsnaam
	 * @var string
	 */
	public $filename;
	/**
	 * Bestandsgrootte in bytes
	 * @var int
	 */
	public $filesize;
	/**
	 * Mime-type van het bestand
	 * @var string 
	 */
	public $mimetype;
	/**
	 * Locatie van bestand
	 * @var Map
	 */
	public $directory;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array();
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array();
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = '';

	/**
	 * Bestaat er een bestand met de naam in de map.
	 */
	public function exists() {
		return is_readable($this->directory . $this->filename) AND is_file($this->directory . $this->filename);
	}

}
