<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.simplehtml.php
# -------------------------------------------------------------------
# Van deze klasse worden alle klassen afgeleid die ervoor
# bedoeld zijn om uiteindelijk HTML uit te kotsen
#
# -------------------------------------------------------------------
# Historie:
# 18-12-2004 Hans van Kranenburg
# . aangemaakt
#

class SimpleHTML {
	//html voor een pagina uitpoepen.
	function view() {

	}
	//eventueel titel voor een pagina geven
	function title($sTitle=false){
		if($sTitle===false){
			return 'C.S.R.-Delft';
		}else{
			return 'C.S.R.-Delft - '.$sTitle;
		}
	}
}

?>
