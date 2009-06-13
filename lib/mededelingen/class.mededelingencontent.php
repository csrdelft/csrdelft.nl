<?php
class MededelingenContent extends SimpleHTML{
	private $selectedMededeling;
	
	public function __construct($mededelingId){
		$this->selectedMededeling=null;
		if($mededelingId!=0)
		{
			try{
				$this->selectedMededeling=new Mededeling($mededelingId);
			} catch (Exception $e) {
				// Do nothing, keeping $selectedMededeling equal null.
			}
		}
	}

	public function view(){
		$content=new Smarty_csr();
		define( 'NIEUWS_ROOT', '/actueel/mededelingen/');

		$content->assign('melding', $this->getMelding());
		$content->assign('nieuws_root', NIEUWS_ROOT);
		$content->assign('selectedMededeling', $this->selectedMededeling);
		$content->assign('ubb', CsrUBB::instance());

		$content->display('mededelingen/mededelingen.tpl');
	}
}
?>