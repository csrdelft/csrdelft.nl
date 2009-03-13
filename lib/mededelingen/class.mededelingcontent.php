<?php
class MededelingContent extends SimpleHTML{
	private $mededeling;
	
	public function _construct(Mededeling $mededeling){
		$this->mededeling=$mededeling;
	}
	
	public function view(){
		$ranken=$this->mededeling->getRanken();
		
		$content=new Smarty_csr();
		
		$content->assign('mededeling', $mededeling);
		$content->assign('nieuws_root', NIEUWS_ROOT);
		$content->assign('csr_pics', CSR_PICS);
		$content->assign('ranken', $ranken);
		
		$content->display('mededelingen/mededeling.tpl');
	}
}
?>