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

?>
