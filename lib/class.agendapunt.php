<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.maaltijd.php
# -------------------------------------------------------------------
# -------------------------------------------------------------------


class AgendaPunt {
	# MySQL connectie
	var $_db;
	# lid-object van ingelogde gebruiker
	var $_lid;
	# id van het agendapunt waar we bewerkingen op uitvoeren
	var $_agendaid;
	# Evt. foutmelding
	var $_error = '';
	
	# maaltijd-record
	var $_agendapunt = false;

	# we gaan bewerkingen uitvoeren op een agendapunt, onder verantwoordelijkheid van een bepaald lid
	# NB!! Gebruik Agenda::isAgendaPunt voor controle of het agendapunt wel bestaat
	function AgendaPunt($agendaid, $lid, $db) {
		$this->_agendaid = (int)$agendaid;
		$this->_lid =& $lid;
		$this->_db =& $db;
		
		# gegevens van het agendapunt inladen
		$result = $this->_db->select("SELECT * FROM agenda WHERE id='{$this->_agendaid}'");
		if (($result !== false) and $this->_db->numRows($result) > 0)
			$this->_agendapunt = $this->_db->next($result);
		
	}

	function getError() {
		$error = $this->_error;
		$this->_error = "";
		return $error;
	}

	function getDatum() {
		$datum = _agendapunt['datum'];
		return $datum;
	}
		
	
	# wat gegevens voor de maaltijdprintlijst
	function getDatum() { return $this->_agendapunt['datum']; }
	function getTijd() { return $this->_agendapunt['tijd']; }
	function getAgendaPuntId() { return $this->_agendaid; }
	# alle info...
	function getInfo() { return $this->_agendapunt; }

}

?>
