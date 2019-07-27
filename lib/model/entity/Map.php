<?php

namespace CsrDelft\model\entity;

use CsrDelft\Orm\Entity\PersistentEntity;

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
	 * Bestaat er een map met het pad.
	 */
	public function exists() {
		return @is_readable($this->path) AND is_dir($this->path);
	}

}
