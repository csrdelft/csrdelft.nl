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
			//print_r($bewerkteLeden[$uid]['corvee_voorkeuren']);
		}		
		return $bewerkteLeden;
	}
		
	public static $sorteer_voorkeur;
	
	//Geef een lijst met alle leden terug, gesorteerd op het gegeven veld.
	//Staat toe om op corvee-voorkeur te sorteren.
	public function getCorveeLedenGesorteerd($sorteer, $sorteer_richting){
		$sorteer_toegestaan = array('uid', 'voorkeur_0', 'voorkeur_1', 'voorkeur_2', 'voorkeur_3', 'voorkeur_4', 'voorkeur_5', 'voorkeur_6', 'voorkeur_7', 'corvee_kwalikok', 'corvee_punten', 'corvee_punten_bonus', 'corvee_vrijstelling', 'corvee_prognose', 'corvee_tekort');
		$sorteer_volgorde_toegestaan = array('asc', 'desc');
		if (!in_array($sorteer, $sorteer_toegestaan) || !in_array($sorteer_richting, $sorteer_volgorde_toegestaan)){
			$this->_error = 'Ongeldige sorteeroptie: ['.$sorteer.' '.$sorteer_richting.']';
			return false;
		}
		
		$sorteer_op_voorkeur = array('voorkeur_0', 'voorkeur_1', 'voorkeur_2', 'voorkeur_3', 'voorkeur_4', 'voorkeur_5', 'voorkeur_6', 'voorkeur_7');
		if(in_array($sorteer, $sorteer_op_voorkeur)){
			$leden = $this->getCorveeLeden('corvee_punten DESC');
			CorveevoorkeurenLijst::$sorteer_voorkeur = (int)substr($sorteer, strlen($sorteer)-1);
			
			function compare_voorkeur_asc($lidentry_a, $lidentry_b) { 
				$a = $lidentry_a['corvee_voorkeuren'][CorveevoorkeurenLijst::$sorteer_voorkeur]; 
				$b = $lidentry_b['corvee_voorkeuren'][CorveevoorkeurenLijst::$sorteer_voorkeur];
			    if ($a == $b) {
			        return 0;
			    }
			    return ($a < $b) ? -1 : 1;				
			} 
			function compare_voorkeur_desc($lidentry_a, $lidentry_b){
				$resultaat_asc = compare_voorkeur_asc($lidentry_a, $lidentry_b);
				return -$resultaat_asc;
			}
						
			if(!usort($leden, 'compare_voorkeur_'.$sorteer_richting)){
				return false;
			}
			return $leden;
		}else{
			$sortering = $sorteer.' '.$sorteer_richting;
			$sortering = $sortering.', corvee_punten DESC';
			return $this->getCorveeLeden($sortering);	
		} 		
	}
}
?>