<?php

require_once 'smarty/libs/Smarty.class.php';

/**
 * CsrSmarty.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class CsrSmarty extends Smarty {

	public function __construct() {
		parent::__construct();

		$this->setTemplateDir(SMARTY_TEMPLATE_DIR);
		$this->setCompileDir(SMARTY_COMPILE_DIR);
		//$this->setConfigDir(SMARTY_CONFIG_DIR); 
		$this->setCacheDir(SMARTY_CACHE_DIR);
		$this->caching = false;

		// frequently used things
		$this->assign('CSR_PICS', CSR_PICS);
		$this->assign('REQUEST_URI', REQUEST_URI);
	}

}
