<?php

/**
 * CsrMemcache.singleton.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Wrapper voor Memchache if available; DummyCache otherwise.
 */
class CsrMemcache {

	/**
	 * Singleton instance
	 * @var CsrMemcache
	 */
	private static $instance;
	/**
	 * Connection established
	 * @var boolean
	 */
	private static $connected = false;

	/**
	 * Get singleton CsrMemcache instance.
	 * 
	 * @return CsrMemcache
	 */
	public static function instance() {
		if (!isset(self::$instance)) {
			if (class_exists('Memcache')) {
				self::$instance = new Memcache();
				self::$connected = self::$instance->connect('unix://' . DATA_PATH . 'csrdelft-cache.socket', 0);
			} else {
				self::$instance = new DummyCache();
			}
		}
		return self::$instance;
	}

	public static function isAvailable() {
		return self::$connected;
	}

	private function __construct() {
		// never called
	}

}

class DummyCache {

	public function __call($name, $arguments) {
		return false;
	}

}
