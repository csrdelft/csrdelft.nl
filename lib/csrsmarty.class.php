<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# csrsmarty.class.php
# -------------------------------------------------------------------

// load Smarty library
require_once('smarty/libs/Smarty.class.php');

class Smarty_csr extends Smarty {

	public function __construct() {
		parent::__construct();

		$this->template_dir = SMARTY_TEMPLATE_DIR;
		$this->compile_dir = SMARTY_COMPILE_DIR;
		//$this->config_dir = ;
		$this->cache_dir = SMARTY_CACHE_DIR;
		$this->caching = false;

		//zet een aantal handige dingen die we veel nodig hebben
		$this->assign('GLOBALS', $GLOBALS);
		$this->assign('CSR_PICS', CSR_PICS);
		$this->assign('CSR_ROOT', CSR_ROOT);
		$this->assign('loginlid', LoginLid::instance());
		$this->assign('ubbHulp', CsrUbb::getUbbHelp());
	}
}
