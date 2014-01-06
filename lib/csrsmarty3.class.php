<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# csrsmarty3.class.php
# -------------------------------------------------------------------

require_once('smarty3/libs/Smarty.class.php');

class Smarty3CSR extends Smarty {

	public function __construct() {
		parent::__construct();
		
		$this->template_dir = SMARTY_TEMPLATE_DIR;
		$this->compile_dir = SMARTY_COMPILE_DIR;
 		//$this->config_dir = ;
		$this->cache_dir = SMARTY_CACHE_DIR;
		$this->caching = false;
		
		//zet een aantal handige dingen die we veel nodig hebben
		$this->assign('CSR_PICS', CSR_PICS);
		$this->assign('CSR_ROOT', CSR_ROOT);
		$this->assign('ubbHulp', CsrUbb::getUbbHelp());
		$this->assign('loginlid', LoginLid::instance());
		$this->assign('GLOBALS', $GLOBALS);
  }
}
