<?php
class MededelingContent extends SimpleHTML{
	private $mededeling;
	
	public function _construct(Mededeling $mededeling){
		$this->mededeling=$mededeling;
	}
	
	public function view(){
		$ranken=$this->mededeling::getRanks();
		
		$content=new Smarty_csr();
		
		$content->assign('mededeling', $this->mededeling);
		$content->assign('nieuws_root', NIEUWS_ROOT);
		$content->assign('ranken', $ranken);
		
		$content->display('mededelingen/mededeling.tpl');
	}
}
?>