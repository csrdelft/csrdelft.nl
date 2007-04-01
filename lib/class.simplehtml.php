<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.simplehtml.php
# -------------------------------------------------------------------
# Van deze klasse worden alle klassen afgeleid die ervoor
# bedoeld zijn om uiteindelijk HTML uit te kotsen
# -------------------------------------------------------------------


class SimpleHTML {
	//html voor een pagina uitpoepen.
	function view() {

	}
	//eventueel titel voor een pagina geven
	function title($sTitle=false){
		if($sTitle===false){
			return 'C.S.R. Delft';
		}else{
			return 'C.S.R. Delft - '.$sTitle;
		}
	}
}

?>
