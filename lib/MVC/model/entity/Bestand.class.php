<?php

require_once 'MVC/model/entity/Map.class.php';

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

}
