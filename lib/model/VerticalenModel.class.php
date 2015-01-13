<?php

/**
 * VerticalenModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class VerticalenModel extends CachedPersistenceModel {

	const orm = 'Verticale';

	protected static $instance;
	/**
	 * Store verticalen array as a whole in memcache
	 * @var boolean
	 */
	protected $memcache_prefetch = true;

	public static function get($letter) {
		return static::instance()->retrieveByPrimaryKey(array($letter));
	}

	protected function __construct() {
		parent::__construct('groepen/');
	}

}
