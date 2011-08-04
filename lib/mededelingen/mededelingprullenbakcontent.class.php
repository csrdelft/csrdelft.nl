<?php
class MededelingPrullenbakContent extends SimpleHTML {
	
	public function __construct(){
		
	}
	
	public function view(){
		$content=new Smarty_csr();

		$content->assign('mededelingen_root', MededelingenContent::mededelingenRoot);
		
		$content->display('mededelingen/prullenbak.tpl');
	}
	
}