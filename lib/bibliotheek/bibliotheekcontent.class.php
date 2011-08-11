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

	public function __construct(Catalogus $catalogus){
		$this->catalogus = $catalogus;
	}

	public function getTitel(){
		return 'Bibliotheek | Catalogus';
	}
	public function view(){
		$smarty=new Smarty_csr();
		$smarty->assign('melding', $this->getMelding());
		$smarty->assign('catalogus', $this->catalogus);
		$smarty->assign('action', $this->action);

		$smarty->display('bibliotheek/catalogus.tpl');
	}

}

/*
 * Catalogus
 */
class BibliotheekBoekstatusContent extends SimpleHtml{

	private $catalogus ;

	public function __construct(Catalogus $catalogus){
		$this->catalogus = $catalogus;
	}
	
	public function getTitel(){
		return 'Bibliotheek | Boekstatus';
	}
	public function view(){
		$smarty=new Smarty_csr();
		$smarty->assign('melding', $this->getMelding());
		$smarty->assign('catalogus', $this->catalogus);
		$smarty->assign('action', $this->action);

		$smarty->display('bibliotheek/boekstatus.tpl');
	}
}
/*
 * Boek weergeven
 */
class BibliotheekBoekContent extends SimpleHtml{

	private $boek;
	private $action='view';

	public function __construct(Boek $boek){
		$this->boek=$boek;
	}
	public function getTitel(){
		return 'Bibliotheek | Boek: '.$this->boek->getTitel();
	}
	public function setAction($action){
		$this->action=$action;
	}
	public function view(){
		$smarty=new Smarty_csr();
		$smarty->assign('melding', $this->getMelding());
		$smarty->assign('boek', $this->boek);
		$smarty->assign('action', $this->action);

		$smarty->display('bibliotheek/boek.tpl');
	}

}
/*
 * Contentclasse voor de boek-ubb-tag
 */
class BoekUbbContent extends SimpleHTML{
	private $boek;
	private $style;
	public function __construct($boekid, $style='default'){
		try{
			require_once 'bibliotheek/boek.class.php';
			$this->boek=new Boek((int)$boekid);
		}catch(Exception $e){
			$this->boek='[boek] Boek [boekid:'.(int)$boekid.'] bestaat niet.';
		}
	}
	public function getHTML(){
		if($this->boek instanceof Boek){
			$content=new Smarty_csr();
			$content->assign('boek', $this->boek);
			return $content->fetch('bibliotheek/boek.ubb.tpl');
		}else{
			return $this->boek;
		}
	}
	public function view(){
		echo $this->getHTML();
	}
}
?>
