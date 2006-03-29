<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.nieuwsbeheercontent.php
# -------------------------------------------------------------------
#
# 
#
# -------------------------------------------------------------------
# Historie:
# 07-03-2006 Jieter
# . gemaakt
#

require_once ('class.simplehtml.php');
require_once ('bbcode/include.bbcode.php');


class NieuwsbeheerContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_nieuws;
	var $_lid;

	### public ###

	function CommissieContent (&$nieuws, &$lid) {
		$this->_nieuws =& $nieuws;
		$this->_lid =& $lid;
	}
	
	function bewerkFormulier($iBerichtID){
		
	}
	function view(){
	
	}
}

?>
