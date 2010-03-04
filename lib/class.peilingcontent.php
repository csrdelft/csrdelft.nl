<?php
/*
 * PeilingContent
 * 
 * Roept template aan.
 */
require_once 'class.peiling.php';

class PeilingContent extends SimpleHTML{
	private $peiling;
	
	function PeilingContent(Peiling $peiling){
		$this->peiling=$peiling;
	}

	public function getHTML($beheer=false){
		$smarty=new Smarty_csr();
		
		$smarty->assign('peiling', $this->peiling);
		$smarty->assign('beheer', $beheer);
		
		return $smarty->fetch('peiling.ubb.tpl');
	} 
	
	public function view(){
		echo $this->getHTML();
	}
}
?>
