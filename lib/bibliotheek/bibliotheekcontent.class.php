<?php
/*
 * bibliotheekcontent.class.php	|	Gerrit Uitslag (klapinklapin@gmail.com)
 *
 *
 */
require_once 'catalogus.class.php';

/*
 * Catalogus
 */
class BibliotheekCatalogusContent extends SimpleHtml{

	private $catalogus ;

	public function __construct(){
		$this->catalogus = new Catalogus();
	}

	public function getTitel(){
		return 'Bibliotheek | Catalogus';
	}
	public function view(){
		$smarty=new Smarty_csr();
		$smarty->assign('melding', $this->getMelding());
		$smarty->assign('catalogus', $this->catalogus);
		$smarty->display('bibliotheek/catalogus.tpl');
	}

}

/*
 * Boek weergeven
 */
class BibliotheekBoekContent extends SimpleHtml{

	private $boek;
	
	public function __construct(Boek $boek){
		$this->boek=$boek;
	}
	public function getTitel(){
		return 'Bibliotheek | Boek: '.$this->boek->getTitel();
	}
	public function view(){
		$smarty=new Smarty_csr();
		$smarty->assign('melding', $this->getMelding());
		$smarty->assign('boek', $this->boek);
		$smarty->display('bibliotheek/boek.tpl');
	}

}

?>
