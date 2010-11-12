<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# maaltijden/class.corveevoorkeurenlijst.php
# -------------------------------------------------------------------
# Deze klasse kan arrays teruggeven met daarin 
# alle leden en hun corvee-eigenschappen. 
# -------------------------------------------------------------------

class CorveevoorkeurenLijst{
	
	//Zoek leden met corvee-eigenschappen, geef de corveevoorkeuren terug in een array	
	private function getCorveeLeden($sort='achternaam'){
		$zoekLeden = Zoeker::zoekLeden('', 'uid', 'alle', $sort, 'leden', array('uid', 'achternaam', 'voornaam', 'tussenvoegsel', 'corvee_voorkeuren', 'corvee_vrijstelling', 'corvee_punten'));
		$bewerkteLeden = array();
		foreach($zoekLeden as $uid=>$lidentry){	
			$bewerkteLeden[$uid] = $lidentry;
			$bewerkteLeden[$uid]['corvee_voorkeuren'] = array();		
			for($index=0; $index<8; $index++){
				$string = $lidentry['corvee_voorkeuren'];				
				$bewerkteLeden[$uid]['corvee_voorkeuren'][$index] = $string[$index];  
			}			
		}		
		return $bewerkteLeden;
	}
	
	private function getCorveeLedenMetPrognose($sort='achternaam'){
		//Haal de lijst met leden met hun corveeprognose op
		$track = new MaalTrack();
		$prognoselijst = $track->getPuntenlijst('uid', 'asc');
		$prognoselijst_assoc = array();
		foreach($prognoselijst as $index=>$entry){
			$prognoselijst_assoc[$entry['uid']] = $entry;
		}
		
		//Haal de lijst met leden met hun corveevoorkeuren op
		$zoekLeden = Zoeker::zoekLeden('', 'uid', 'alle', $sort, 'leden', array('uid', 'achternaam', 'voornaam', 'tussenvoegsel', 'corvee_voorkeuren', 'corvee_vrijstelling', 'corvee_punten'));
		
		//Combineer de twee lijsten en zet de corveevoorkeuren goed.
		$bewerkteLeden = array();		
		foreach($zoekLeden as $uid=>$lidentry){
			$bewerkteLeden[$uid] =  $lidentry;
			$bewerkteLeden[$uid]['corvee_voorkeuren'] = array();		
			for($index=0; $index<8; $index++){
				$string = $lidentry['corvee_voorkeuren'];				
				$bewerkteLeden[$uid]['corvee_voorkeuren'][$index] = $string[$index];  
			}
			
			$prognose_entry = $prognoselijst_assoc[$lidentry['uid']];
			$bewerkteLeden[$uid]['corvee_prognose'] = $prognose_entry['corvee_prognose']; 			
		}		
		return $bewerkteLeden;
	}
		
	//Geef een lijst met alle leden terug, gesorteerd op het gegeven veld.
	//Staat toe om op corvee-voorkeur te sorteren.
	public function getCorveeLedenGesorteerd($sorteer, $sorteer_richting){
		$sorteer_toegestaan = array('uid', 'voorkeur_0', 'voorkeur_1', 'voorkeur_2', 'voorkeur_3', 'voorkeur_4', 'voorkeur_5', 'voorkeur_6', 'voorkeur_7', 'corvee_kwalikok', 'corvee_punten', 'corvee_punten_bonus', 'corvee_vrijstelling', 'corvee_prognose', 'corvee_tekort');
		$sorteer_volgorde_toegestaan = array('asc', 'desc');
		if (!in_array($sorteer, $sorteer_toegestaan) || !in_array($sorteer_richting, $sorteer_volgorde_toegestaan)){
			$this->_error = 'Ongeldige sorteeroptie: ['.$sorteer.' '.$sorteer_richting.']';
			return false;
		}
		
		$sorteer_meteen = array('uid', 'corvee_kwalikok', 'corvee_punten', 'corvee_punten_bonus', 'corvee_vrijstelling');
		$sorteer_achteraf = array('corvee_prognose', 'corvee_tekort', 'voorkeur_0', 'voorkeur_1', 'voorkeur_2', 'voorkeur_3', 'voorkeur_4', 'voorkeur_5', 'voorkeur_6', 'voorkeur_7');
		
		//Bepaal met welke sortering de lijst opgehaald moet worden
		$sortering = 'corvee_punten desc';
		if(in_array($sorteer, $sorteer_meteen)){
			$sortering = $sorteer.' '.$sorteer_richting;
			$sortering = $sortering.', corvee_punten DESC';
		}
		//Haal de lijst met leden op
		$leden = $this->getCorveeLedenMetPrognose($sortering);
		
		//Sorteer de lijst als dit niet meteen kon
		if(in_array($sorteer, $sorteer_achteraf)){
			//Bepaal hoe er gesorteerd moet worden
			$sorteer_op_voorkeur = array('voorkeur_0', 'voorkeur_1', 'voorkeur_2', 'voorkeur_3', 'voorkeur_4', 'voorkeur_5', 'voorkeur_6', 'voorkeur_7');
			$sorteer_richting = ($sorteer_richting === 'asc')? 1 : -1;				
			$comparef = null;
			if(in_array($sorteer, $sorteer_op_voorkeur)){
				$sorteer_voorkeur = (int)substr($sorteer, strlen($sorteer)-1);				
				$comparef = function($lidentry_a, $lidentry_b) use ($sorteer_voorkeur,$sorteer_richting){
					$a = $lidentry_a['corvee_voorkeuren'][$sorteer_voorkeur]; 
					$b = $lidentry_b['corvee_voorkeuren'][$sorteer_voorkeur]; 
				    					
					if ($a == $b) {
				        return 0;
				    }
				    $result = (($a < $b) ? -1 : 1);
					if (!is_numeric($a)) { $result = -1; }
					if (!is_numeric($b)) { $result = 1; }
				    
				    return $sorteer_richting*$result;				
				};				
			}else {
				//Sorteer op prognose of tekort							
				$comparef = function($lidentry_a, $lidentry_b) use ($sorteer,$sorteer_richting){
					$a = $lidentry_a[$sorteer]; 
					$b = $lidentry_b[$sorteer];
				    					
					if ($a == $b) {
				        return 0;
				    }
				    $result = (($a < $b) ? -1 : 1);
					if (!is_numeric($a)) { $result = -1; }
					if (!is_numeric($b)) { $result = 1; }
				    
				    return $sorteer_richting*$result;				
				}; 										
			}
			//Sorteer de lijst met de bepaalde functie
			if(!usort($leden, $comparef)){
				return false;
			}
		}
		return $leden;
	}
}
?>