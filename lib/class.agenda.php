<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.agenda.php
# -------------------------------------------------------------------
# Agenda bevat de functies voor de Agenda
# - agendapunt aanmaken
# - agendapunt bewerken
# - agendapunt verwijderen
# - lijst van agendapunten opvragen
# -------------------------------------------------------------------

class Agenda {
	# MySQL connectie
	var $_db;
	# Ingelogde persoon
	var $_lid;
	# evt. foutboodschap
	var $_error;

	function Agenda(&$lid, &$db) {
		$this->_lid =& $lid;
		$this->_db =& $db;
	}
	
	function getError() { $error = $this->_error; $this->_error = ""; return $error; }
	
	# datum - dag waarop de activiteit is
	# tijd - tijdstip waarop de activiteit begint
	# tekst - beschrijving van de activiteit
	function addAgendaPunt($datum, $tijdstip, $tekst) {
		$datum = (int)$datum;
		$tijd = $this->_db->escape($tijd);
		$tekst = mb_substr($tekst, 0, 200);
		$tekst = $this->_db->escape($tekst);

		# bij fouten, niet doorgaan, false teruggeven.
		if(!$this->validateAgendaPunt($datum, $tijd, $tekst)){
			return false;
		}
			
		# voeg het agendapunt toe en geef het id terug, of false als het  niet gelukt is.
		$agendapunt="
			INSERT INTO 
				agenda 
			(
				datum, tijd, tekst
			)VALUES(
				'".$datum."', '".$tijd."', '".$tekst."'
			);";
		
		if (!$this->_db->query($agendapunt)){
			$this->_error="Er is iets mis met de database/query";
			return false;
		}else{
			$agendapunt = new AgendaPunt ($this->_db->insert_id(), $this->_lid, $this->_db);
			return $agendapunt->getAgendaPuntId();
		}
	}
	
	# bestaand agendapunt bewerken. Niet veel verschil met addAgendaPunt, behalve dat hier nog even 
	# gekeken wordt of het agendapunt wel bestaat.
	function editAgendaPunt($agendaid, $datum, $tijd, $tekst){
		if($agendaid!=(int)$agendaid){
			$this->_error="Ongeldig agendaPuntId opgegeven.";
			return false;
		}
		if(!$this->isAgendaPunt($agendaid)){
			$this->_error="Opgegeven agendapunt bestaat niet.";
			return false;
		}
		
		$datum = (int)$datum;
		$tijd = $this->_db->escape($tijd);
		$tekst = mb_substr($tekst, 0, 200);
		$tekst = $this->_db->escape($tekst);
		
		# bij fouten, niet doorgaan, false teruggeven.
		if(!$this->validateAgendaPunt($datum, $tijd, $tekst)){
			return false;
		}

		$agendapunt="
			UPDATE 
				agenda
			SET
				datum=".$datum.",
				tijd='".$tijd."',
				tekst='".$tekst."
			WHERE 
				id=".$agendaid."
			LIMIT 1;";
		if(!$this->_db->query($agendapunt)){
			$this->_error="Er is iets mis met de database/query";
			return false;
		}else{
			$agendapunt = new AgendaPunt ($agendaid, $this->_lid, $this->_db);
			return $agendapunt;
		}
	}
	
	# deze methode valideert de gemeenschappelijke waarden van addAgendaPunt en editAgendaPunt.
	# controle op specifieke dingen voor editAgendaPunt gebeurt nog in de methode zelf.
	function validateAgendaPunt($datum, $tijd, $tekst){
		# controleer of de datum niet in het verleden ligt
		if ($datum < time()) {
			$this->_error = "Het tijdstip van de activiteit moet in de toekomst liggen"; 
			return false;
		}
		
		# tekst max 200 karakters
		if (!is_utf8($tekst)) {
			$this->_error = "De omschrijving bevat ongeldige tekens."; 
			return false;
		}
		
		return true;
	}

	function removeAgendaPunt($agendaid) {
		if (!is_numeric($agendaid)) {
			$this->_error = "Gebruik een numerieke agendapunt-id waarde om te verwijderen";
			return false;			
		}
		# kijk of het agendapunt wel bestaat.
		if(!$this->isAgendaPunt($agendaid)){
			$this->_error = "Dit agendapunt bestaat niet";
			return false;			
		}
		
		# verwijder het agendapunt
		$agendapunt="DELETE FROM agenda WHERE id=".$agendaid;
		
		return $this->_db->query($agendapunt);		
	}
	
	
	# haalt één enkel agendapunt op ter bewerking
	function getAgendaPunt($agendaid){
		$agendaid=(int)$agendaid;
		if($agendaid==0){
			$this->_error="Geen geldig agendapunt-id";
			return false;
		}
		$sAgendaPuntQuery="
			SELECT 
				* 
			FROM 
				agenda 
			WHERE 
				id=".$agendaid." 
			LIMIT 1;";
		$rAgendaPunt=$this->_db->query($sAgendaPuntQuery);
		$aAgendaPunt=$this->_db->next($rAgendaPunt);
		
		return array(
			'id' => $aAgendaPunt['id'],
			'datum' => $aAgendaPunt['datum'],
			'prijs' => $aAgendaPunt['prijs'],
			'tekst' => $aAgendaPunt['tekst']);
		
		$this->_db->result2array($rAgendaPunt);
	}
	
	# haalt agendapunten uit de agendatabel op
	function getAgendaPunten($van = 0, $tot = 0) {
		# kijk in db en haal alle maaltijden op waarbij de begintijd
		# na $van is, en voor $tot

		# meestal zal voor $van time() gebruikt worden, als er niets is opgegeven
		# dan wordt ook de huidige tijd gebruikt
		if ($van == 0) $van = time();
		
		# als $tot niet is opgegeven, of 0 is, dan worden alle maaltijden vanaf
		# van teruggegeven, gesorteerd op tijd
		$tot = (int)$tot;
		$totsql = ($tot != 0) ? "datum < '".$tot."'" : "1";
		
		$agendapunten = array();
		$sAgendaPuntenQuery="
			SELECT 
				* 
			FROM 
				maaltijd 
			WHERE 
				datum > '".$van."' AND ".$totsql." 
			ORDER BY 
				datum ASC, tijd ASC;";	
		$result=$this->_db->select($sAgendaPuntenQuery);
		if (($result !== false) and $this->_db->numRows($result) > 0) {
			while ($record = $this->_db->next($result)) {
				$agendapunten[] = $record;
			}
		}
		# id, datum, prijs, tekst
		return $agendapunten;
	}

	# kijkt of er een agendapunt bestaat met deze agendaid
	# te gebruiken alvorens een object agendapunt aan te maken
	function isAgendaPunt($agendaid) {
		if (!is_numeric($agendaid)) {
			$this->_error = "Het opgegeven agendapunt bestaat niet.";
			return false;
		}
		$result = $this->_db->select("SELECT * from `agenda` WHERE `id` = '$agendaid'");	
		if (($result === false) or $this->_db->numRows($result) == 0) {
			$this->_error = "Het opgegeven agendapunt bestaat niet.";
			return false;
		}
		return true;
	}
	

}

?>
