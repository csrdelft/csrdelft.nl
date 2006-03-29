<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.woonoord.php
# -------------------------------------------------------------------
#
# -------------------------------------------------------------------
# Historie:
# 28-08-2005 Hans van Kranenburg
# . gemaakt
#

require_once ('class.mysql.php');

class Woonoord {
	### private ###
	var $_db;
	var $_woonoord;

	var $_status = array ('huis' => 'W_HUIS', 'kot' => 'W_KOT', 'overig' => 'W_OVERIG');

	### public ###
	
	
	function Woonoord($db) {
		$this->_db =& $db;
	}

	function loadWoonoord($naam) {
		/*
		if (!preg_match("/^\w+$/",$cie)) $cie = "Commissies";
		$db = new MySQL();

		# eerst de opgegeven naam proberen...
		$result = $db->select("SELECT * FROM `commissie` WHERE `naam` = '{$cie}'");
        if ($result !== false and $db->numRows($result) > 0) {
			$this->_cie = $db->next($result);
		} else {
			# anders de standaard-info
			$result = $db->select("SELECT * FROM `commissie` WHERE `naam` = 'Commissies'");
        	if ($result !== false and $db->numRows($result) > 0) {
				$this->_cie = $db->next($result);
			} else die("Webmaster, ga die Commissietabel repareren met je donder!");
		}
		*/
	}
	#function getCommissie() { return $this->_cie; }

	function getAll($status) {
		# kijken of er een geldige woonoord-status is opgegeven
		if (!array_key_exists($status, $this->_status)) return array();
		$status = $this->_status[$status];
		$woonoorden = array();

		$result = $this->_db->select("SELECT * FROM `woonoord` WHERE `status` = '{$status}' ORDER BY `sort`");
        if ($result !== false and $this->_db->numRows($result) > 0) {
			while ($woonoord = $this->_db->next($result)) $woonoorden[] = $woonoord;
		}

		return $woonoorden;
	}

	function getBewoners($woonoordid) {
		$bewoners = array();

		# geen gezooi graag
		$woonoordid = (int)$woonoordid;
		$result = $this->_db->select("SELECT `voornaam` , `tussenvoegsel` , `achternaam` FROM `lid` WHERE `uid` IN ( SELECT `uid` FROM `bewoner` WHERE `woonoordid` = '{$woonoordid}' )");
        if ($result !== false and $this->_db->numRows($result) > 0) {
			while ($bewoner = $this->_db->next($result)) $bewoners[] = $bewoner;
		}

		return $bewoners;
	}
}

?>
