<?php

namespace CsrDelft\model\entity;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class Map
{
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
	 * Bestaat er een map met het pad.
	 */
	public function exists()
	{
		return @is_readable($this->path) && is_dir($this->path);
	}
}
