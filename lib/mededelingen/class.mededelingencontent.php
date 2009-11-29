<?php
class MededelingenContent extends SimpleHTML{
	private $geselecteerdeMededeling;
	private $paginaNummer;
	private $paginaNummerOpgevraagd;
	
	const aantalTopMostBlock=3;
	const aantalPerPagina=6;
	const mededelingenRoot='/actueel/mededelingen/';
	
	public function __construct($mededelingId){
		$this->geselecteerdeMededeling=null;
		$this->paginaNummer=1;
		$this->paginaNummerOpgevraagd=false;

		if($mededelingId!=0){
			try{
				$this->geselecteerdeMededeling=new Mededeling($mededelingId);
				if(($this->geselecteerdeMededeling->isPrive() AND !LoginLid::instance()->hasPermission('P_LEDEN_READ')) OR
						($this->geselecteerdeMededeling->getZichtbaarheid()=='wacht_goedkeuring' AND !Mededeling::isModerator())){
					// De gebruiker heeft geen rechten om dit bericht te bekijken, dus we resetten het weer.
					$this->geselecteerdeMededeling=null;
				}
			} catch (Exception $e) {
				// Doe niets, zodat $geselecteerdeMededeling gelijk blijft aan null.
			}
		}
		if($this->geselecteerdeMededeling===null){
			// Als er minstens één 'topmost' mededeling is, maak dat de geselecteerde.
			// Anders, hou $this->geselecteerdeMededeling gelijk aan null.
			$topMost=Mededeling::getTopmost(self::aantalTopMostBlock); // Haal de n belangrijkste mededelingen op.
			if(isset($topMost[0])){
				$this->geselecteerdeMededeling=$topMost[0];
			}
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

		$content->assign('melding', $this->getMelding());
		$content->assign('nieuws_root', self::mededelingenRoot);
		
		$content->assign('lijst', Mededeling::getLijstVanPagina($this->paginaNummer, self::aantalPerPagina));
		$content->assign('geselecteerdeMededeling', $this->geselecteerdeMededeling);
		
		$content->assign('huidigePagina', $this->paginaNummer);
		$content->assign('totaalAantalPaginas', (ceil(Mededeling::getAantal()/self::aantalPerPagina)));

		$content->display('mededelingen/mededelingen.tpl');
	}
	
	public function getTopBlock($oudledenVersie=false){
		$content=new Smarty_csr();
		
		$topMost=Mededeling::getTopmost(self::aantalTopMostBlock, $oudledenVersie);

		$content->assign('mededelingenRoot', self::mededelingenRoot);
		$content->assign('topmost', $topMost);

		return $content->fetch('mededelingen/mededelingentopblock.tpl');
	}
}
?>
