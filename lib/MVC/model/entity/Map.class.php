<?php

/**
 * Map.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Map extends PersistentEntity {

	/**
	 * Mapnaam
	 * @var string
	 */
	public $dirname;
	/**
	 * Volledig pad (met trailing slash)
	 * @var string
	 */
	public $path;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array();

}
