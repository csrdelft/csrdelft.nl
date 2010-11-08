<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# maaltijden/class.corveelid.php
# -------------------------------------------------------------------
# CorveeLid bevat functies om de eigenschappen
# van een Lid die met corvee te maken hebben aan
# te passen. Dit zijn onder meer:
# - Het kwalikok zijn
# - Het aantal corvee-bonuspunten
# - De vrijstelling van corveetaken
# -------------------------------------------------------------------

class CorveeLid {
	private $lid;
	
	public function __construct($lid){
		if(!$lid instanceof Lid){
			throw new Exception('Geen correct Lid-object opgegeven.');
		}
		$this->lid = $lid;
	}	
	
	public function getCorveePunten(){
		return $this->lid->getProperty('corvee_punten');
	}
	
	public function getBonusPunten(){
		return $this->lid->getProperty('corvee_punten_bonus');
	}
	
	public function setKwalikok($corvee_kwalikok){
		return $this->lid->setProperty('corvee_kwalikok', $corvee_kwalikok);
	}

	public function setCorveePunten($corvee_punten){
		return $this->lid->setProperty('corvee_punten', $corvee_punten);
	}
	
	public function setBonusPunten($corvee_punten_bonus){
		return $this->lid->setProperty('corvee_punten_bonus', $corvee_punten_bonus);	
	}
	
	public function setVrijstelling($corvee_vrijstelling){
		return $this->lid->setProperty('corvee_vrijstelling', $corvee_vrijstelling);	
	}
	
	//Wijzig het kwalikok zijn, het aantal bonuspunten en het percentage vrijstelling van corvee. 
	public function setAlles($corvee_kwalikok, $corvee_punten_bonus, $corvee_vrijstelling){		
		// lid bewerken
		$isgelukt = $this->setKwalikok($corvee_kwalikok);
		$isgelukt = $isgelukt && $this->setBonusPunten($corvee_punten_bonus);
		$isgelukt = $isgelukt && $this->setVrijstelling($corvee_vrijstelling);
		$isgelukt = $isgelukt && $this->save();
		return $isgelukt;
	}
	
	//Sla alle gemaakte wijzigingen op, flush de cache. 
	public function save(){
		return $this->lid->save();		
	}
}