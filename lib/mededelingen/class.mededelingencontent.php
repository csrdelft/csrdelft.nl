<?php
class MededelingenContent extends SimpleHTML{
	private $geselecteerdeMededeling;
	private $paginaNummer;
	private $paginaNummerOpgevraagd;
	private $topMost;
	
	const aantalTopMostBlock=3;
	const aantalPerPagina=6;
	
	public function __construct($mededelingId){
		$this->geselecteerdeMededeling=null;
		$this->paginaNummer=1;
		$this->paginaNummerOpgevraagd=false;

		$this->topMost=Mededeling::getTopmost(self::aantalTopMostBlock); // Haal de n belangrijkste mededelingen op.
		
		if($mededelingId!=0){
			try{
				$this->geselecteerdeMededeling=new Mededeling($mededelingId);
			} catch (Exception $e) {
				// Doe niets, zodat $geselecteerdeMededeling gelijk blijft aan null.
			}
		}
		if($this->geselecteerdeMededeling===null){
			// Als er minstens één 'topmost' mededeling is, maak dat de geselecteerde.
			// Anders, hou $this->geselecteerdeMededeling gelijk aan null.
			if(isset($this->topMost[0]))
				$this->geselecteerdeMededeling=$this->topMost[0];
		}
	}
	
	public function setPaginaNummer($pagina){
		if(is_numeric($pagina) AND $pagina>=1){
			$this->paginaNummerOpgevraagd=true;
			$this->paginaNummer=$pagina;
		}
	}

	public function view(){
		if(!$this->paginaNummerOpgevraagd){
			$this->paginaNummer = $this->geselecteerdeMededeling->getPaginaNummer();
		}
		
		$content=new Smarty_csr();
		define( 'NIEUWS_ROOT', '/actueel/mededelingen/');

		$content->assign('melding', $this->getMelding());
		$content->assign('nieuws_root', NIEUWS_ROOT);
		
		$content->assign('topmost', $this->topMost);
		$content->assign('lijst', Mededeling::getLijstVanPagina($this->paginaNummer, self::aantalPerPagina));
		$content->assign('geselecteerdeMededeling', $this->geselecteerdeMededeling);
		
		$content->assign('huidigePagina', $this->paginaNummer);
		$content->assign('totaalAantalPaginas', (ceil(Mededeling::getAantal()/self::aantalPerPagina)));

		$content->display('mededelingen/mededelingen.tpl');
	}
}
?>
