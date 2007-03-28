<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.dbmenu.php
# -------------------------------------------------------------------
# Een menu incl permissies uit de database trekken.
# De menuopties die niet overeenkomen met de permissies die de
# gebruiker heeft worden niet getoond.
# -------------------------------------------------------------------


class DBMenu {

	### private ###

	var $_lid;
	var $_menu;
	var $_db;

	### public ###

	function DBMenu($naam, &$lid, &$db) {
		$this->_lid =& $lid;
		$this->_db =& $db;

		$this->_menu = array();

		# menu ophalen
		$result = $this->_db->select("SELECT * FROM `menu` WHERE `menu` = '{$naam}' ORDER BY `volgnummer` ASC");
		# checken of er wat in zit
   	    if ($result and $this->_db->numRows($result) > 0) {
			while ($menuoptie = $this->_db->next($result)) {
				$this->_menu[] = $menuoptie;
			}
		} else die ("Help! Menu bestaat niet!");
		return sizeof($this->_menu);
	}

	function getMenuTitel() { return $this->_menu[0]['tekst']; }

	function view() {
		echo '<br />';
		for ($i=1; $i < sizeof($this->_menu); $i++) {
			# mogen we deze optie wel gebruiken?
			if ($this->_lid->hasPermission($this->_menu[$i]['permission'])) {
				# zit er een link in?
				if ($this->_menu[$i]['link'] != '')
					echo '<a href="' . htmlspecialchars($this->_menu[$i]['link']) . '">';
				if ($this->_menu[$i]['tekst'] != '')
					echo htmlspecialchars($this->_menu[$i]['tekst']);
				if ($this->_menu[$i]['link'] != '')
					echo '</a>';
				echo "<br />\n";
			}
		}
		echo '<br />';
	}
}

?>
