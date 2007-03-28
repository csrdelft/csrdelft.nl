<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.includer.php
# -------------------------------------------------------------------


class Includer {

	### private ###

	var $_sub = '';
	var $_page = '';
	
	var $_lid;
	### public ###

	function Includer ($sub = '', $page = '') {
	
		# controleren of het een geldige naam is... platte namespace
		if ($sub != '') {
			if (preg_match('/^[-\w\.]+$/',$sub)) $this->_sub = $sub;
			else die ("Includer: Invalid path info");
		}

		if ($page == '') die ("Includer: No file specified");
		elseif (preg_match('/^[-\w\.]+$/',$page)) $this->_page = $page;
		else die ("Includer: Invalid file info");
	}
	function getTitel(){
		$titel=str_replace(array('.html', '.htm', '.php'), '', $this->_page);
		return ucfirst($titel);
	}
	function view() {
		if ($this->_sub == '') $filename = $this->_page;
		else $filename = "{$this->_sub}/{$this->_page}";

		# includeren...
		include 'tekst/'.$filename;
	}

}

?>
