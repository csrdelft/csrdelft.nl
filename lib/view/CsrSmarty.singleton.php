<?php

require_once 'smarty/libs/Smarty.class.php';

/**
 * CsrSmarty.singleton.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class CsrSmarty extends Smarty {

	/**
	 * Singleton instance
	 * @var Database
	 */
	private static $instance;

	/**
	 * Get singleton Database instance.
	 * 
	 * @return Database
	 */
	public static function instance() {
		if (!isset(self::$instance)) {
			self::$instance = new CsrSmarty();

			self::$instance->setTemplateDir(SMARTY_TEMPLATE_DIR);
			self::$instance->setCompileDir(SMARTY_COMPILE_DIR);
			self::$instance->addPluginsDir(SMARTY_PLUGIN_DIR);
			//self::$instance->setConfigDir(SMARTY_CONFIG_DIR); 
			self::$instance->setCacheDir(SMARTY_CACHE_DIR);
			self::$instance->caching = false;

			// frequently used things
			self::$instance->assign('REQUEST_URI', REQUEST_URI);
		}
		return self::$instance;
	}

}
