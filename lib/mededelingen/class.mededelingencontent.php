<?php
class MededelingenContent extends SimpleHTML{
	private $selectedMededeling;
	private $paginaNummer;
	
	const aantalPerPagina = 20;
	
	public function __construct($mededelingId){
		$this->selectedMededeling=null;
		$this->paginaNummer=1;
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
	
	public function setPaginaNummer($pagina){
		if(is_numeric($pagina) AND $pagina>=1)
			$this->paginaNummer=$pagina;
	}

	public function view(){
		$content=new Smarty_csr();
		define( 'NIEUWS_ROOT', '/actueel/mededelingen/');

		$content->assign('melding', $this->getMelding());
		$content->assign('nieuws_root', NIEUWS_ROOT);
		$content->assign('csr_pics', CSR_PICS);
		
		$content->assign('topmost', Mededeling::getTopMost());
		$content->assign('lijst', Mededeling::getLijstVanPagina($this->paginaNummer, self::aantalPerPagina));
		// The following attribute can't be null. Otherwise, the page will
		// not display a full Mededeling.
		$content->assign('selectedMededeling', $this->selectedMededeling);
		$content->assign('ubb', CsrUBB::instance());
		
		$content->assign('huidigePagina', $this->paginaNummer);
		$content->assign('totaalAantalPaginas', (ceil(Mededeling::getAantal()/self::aantalPerPagina)));

		$content->display('mededelingen/mededelingen.tpl');
	}
}
?>