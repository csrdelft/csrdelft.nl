<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.csrdelft.php
# -------------------------------------------------------------------
# csrdelft is de klasse waarbinnen een pagina in elkaar wordt gezooid
# -------------------------------------------------------------------


require_once('csrdelft.class.php');

class vbmain extends csrdelft {


	function vbmain($body){
		parent::__construct($body,'vb/vb',99);
		$this->addStylesheet('vb.css');
		$this->addScript('../../vb/jsonencode.js');
		require_once('menu.class.php');
	}

function view() {
		header('Content-Type: text/html; charset=UTF-8');
		$csrdelft=new Smarty_csr();
		$csrdelft->assign_by_ref('csrdelft', $this);
		require_once('class.navigator.php');
		$csrdelft->caching=false;

		$nav = navigator::instance();
		$csrdelft->assign_by_ref('navbar',$nav);

		if(defined('DEBUG') AND LoginLid::instance()->hasPermission('P_ADMIN')){
			 $csrdelft->assign('db', MySql::instance());
		}

		$csrdelft->display('vb/vbcsrdelft.tpl');

		
		//als er een error is geweest, die unsetten...
		if(isset($_SESSION['auth_error'])){ unset($_SESSION['auth_error']); }
	}

}

?>
