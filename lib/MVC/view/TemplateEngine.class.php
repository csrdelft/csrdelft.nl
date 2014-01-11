<?php

require_once('smarty/libs/Smarty.class.php');

/**
 * TemplateEngine.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * 
 * The template engine compiles the templates
 * and displays them to the user.
 * 
 */
class TemplateEngine extends Smarty {

	public function __construct() {
		parent::__construct();

		$this->template_dir = SMARTY_TEMPLATE_DIR;
		$this->compile_dir = SMARTY_COMPILE_DIR;
		//$this->config_dir = ;
		$this->cache_dir = SMARTY_CACHE_DIR;
		$this->caching = false;

		// frequently used things
		$this->assign('GLOBALS', $GLOBALS);
		$this->assign('CSR_PICS', CSR_PICS);
		$this->assign('loginlid', LoginLid::instance());
	}

}

?>