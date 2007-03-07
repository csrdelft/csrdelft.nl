<?php

# Camping CMS
# OogOpslag Internet (c)2005
# Hans van Kranenburg

# class.state.php

class State {
	### private ###
	var $_mystate;
	var $_myurl;

	### public ###
	function State($mystate = 'none', $myurl = '') {
		$this->setMyState($mystate);
		if ($myurl == '') $myurl = $_SERVER['PHP_SELF'];
		$this->setMyUrl($myurl);
	}

	function setMyState($mystate) { $this->_mystate = $mystate; }
	function getMyState() { return $this->_mystate; }
	function setMyUrl($myurl) { $this->_myurl = $myurl; }

	# Met de parameter append krijg je een url terug waaraan een optie toegevoegd kan worden
	# We gaan er vanuit dat de enige ? die voor zal komen die is als begin van de parameters
	function getMyUrl($append = false) {
		if ($append) {
			# als je het meteen achter elkaar zet, dus $this->_myurl . (strpos($this->_my.... werkt het niet
			$dinges = $this->_myurl;
			//$dinges .= (strpos($this->_myurl, '?') === false) ? '?' : '';
			return $dinges;
		}
		return $this->_myurl;
	}
}

?>
