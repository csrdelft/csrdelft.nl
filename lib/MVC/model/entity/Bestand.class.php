<?php

require_once 'MVC/model/entity/Map.class.php';

/**
 * Bestand.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Bestand {

	/**
	 * Naam
	 * @var string
	 */
	public $bestandsnaam;
	/**
	 * Bestandsgrootte in bytes
	 * @var int
	 */
	public $size;
	/**
	 * Mime-type van het bestand
	 * @var string 
	 */
	public $mimetype;
	/**
	 * Locatie van bestand
	 * @var Map
	 */
	public $map;

}
