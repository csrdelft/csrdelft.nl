<?php

namespace CsrDelft\model\entity;

/**
 * Bestand.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class Bestand
{
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
	 * @var string
	 */
	public $directory;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = [];
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = [];
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = '';

	/**
	 * Bestaat er een bestand met de naam in de map.
	 *
	 * @return bool
	 */
	public function exists()
	{
		return @is_readable($this->directory . '/' . $this->filename) and
			is_file($this->directory . '/' . $this->filename);
	}
}
