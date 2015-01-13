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

	/**
	 * Get uid of verticale leider.
	 * 
	 * @param Verticale $verticale
	 * @return string
	 */
	public function getVerticaleLeider(Verticale $verticale) {
		return Database::instance()->sqlSelect(array('uid'), 'lid', 'verticale = ? AND motebal = 1', array($verticale->letter), null, null, 1)->fetchColumn();
	}

}
