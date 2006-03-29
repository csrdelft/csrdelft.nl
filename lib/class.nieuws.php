<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.nieuws.php
# -------------------------------------------------------------------
# Verzorgt het opvragen en opslaan van nieuwsberichten.
# Wordt o.a. door NieuwsContent gebruikt
# -------------------------------------------------------------------
# Historie:
# 29-12-2004 Hans van Kranenburg
# . gemaakt
#

require_once ('class.mysql.php');

class Nieuws {

	### private ###

	var $_messages = array();
	var $_loadhidden = false;
	
	var $_db;
	function Nieuws(&$db){
		$this->_db=$db;
	}
	### public ###

	#
	# Inladen nieuwsberichten
	# $id == 0 -> alles inladen
	# $id != 0 -> alleen opgegeven nummer
	#
	function loadMessages($id = 0) {
		# opschonen cache
		$this->_messages = array();

		# ophalen van de informatie
		$result = false; $where = ''; $id = (int)$id;

		$query['select'][] = '*';
		$query['from'] = '`nieuws`';
		$query['orderby'] = '`datum` DESC';

		if ($id != 0) $query['where'][] = "`id` = {$id}";
		if (!$this->_loadhidden) $query['where'][] = "`hidden` = '0'";
		$result = $this->_db->select_a($query);

		# checken of er wat in zit
        if ($result !== false and $this->_db->numRows($result) > 0) {
			while ($message = $this->_db->next($result)) {
				$this->_messages[] = $message;
			}
		}

		return sizeof($this->_messages);
	}

	# geef de array met nieuwsberichtjes
	function getMessages() { return $this->_messages; }

	# wel of niet verborgen berichtjes ophalen
	function setLoadHidden($loadhidden) { $this->_loadhidden = $loadhidden; }
}

?>
