<?php

require_once 'mededelingencontent.class.php';

class MededelingTopDrieOverzichtContent extends SimpleHTML {
	
	public function __construct(){
		
	}
	
	public function view(){
		$content=new TemplateEngine();

		$content->assign('mededelingen_root', MededelingenContent::mededelingenRoot);
		
		$content->display('mededelingen/top3overzicht.tpl');
	}
	
}