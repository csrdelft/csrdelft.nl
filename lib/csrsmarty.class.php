<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.csrsmarty.php
# -------------------------------------------------------------------


// load Smarty library
require_once('smarty/libs/Smarty.class.php');

class Smarty_csr extends Smarty {

	public function __construct(){
		$this->Smarty();
		$this->template_dir = SMARTY_TEMPLATE_DIR;
		$this->compile_dir = SMARTY_COMPILE_DIR;
 		//$this->config_dir = ;
		$this->cache_dir = SMARTY_CACHE_DIR;
		$this->caching = false;

		//zet een aantal handige dingen die we veel nodig hebben
		$this->assign('csr_pics', CSR_PICS);
		$this->assign('CSR_ROOT', CSR_ROOT);
		$this->assign('ubbHulp', CsrUbb::getUbbHelp());
		$this->assign('loginlid', LoginLid::instance());
  }

}
?>
