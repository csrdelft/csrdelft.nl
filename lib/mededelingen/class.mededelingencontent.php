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
				// Do nothing, keeping $selectedMededeling equal to null.
			}
		}
		else
		{
			$topmost=Mededeling::getTopmost();
			// If there is at least one topmost, make it the selected one.
			// Otherwise, keep $this->selectedMededeling equal to null.
			if(isset($topmost[0]))
				$this->selectedMededeling=$topmost[0];
		}
	}

	public function view(){
		$content=new Smarty_csr();
		define( 'NIEUWS_ROOT', '/actueel/mededelingen/');

		$content->assign('melding', $this->getMelding());
		$content->assign('nieuws_root', NIEUWS_ROOT);
		$content->assign('csr_pics', CSR_PICS);
		
		$content->assign('topmost', Mededeling::getTopMost());
		// The following attribute can't be null. Otherwise, the page will
		// not display a full Mededeling.
		$content->assign('selectedMededeling', $this->selectedMededeling);
		$content->assign('ubb', CsrUBB::instance());

		$content->display('mededelingen/mededelingen.tpl');
	}
}
?>