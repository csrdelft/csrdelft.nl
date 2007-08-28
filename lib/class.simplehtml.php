<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.simplehtml.php
# -------------------------------------------------------------------
# Van deze klasse worden alle klassen afgeleid die ervoor
# bedoeld zijn om uiteindelijk HTML uit te kotsen
# -------------------------------------------------------------------


class SimpleHTML {
	
	
	private $_sMelding=false;
	//html voor een pagina uitpoepen.
	function view() {

	}
	function getMelding(){
		if(isset($_SESSION['melding']) AND $_SESSION['melding']!=''){
			$sError='<div id="melding">'.mb_htmlentities(trim($_SESSION['melding'])).'</div>';
			//maar één keer tonen, de melding.
			unset($_SESSION['melding']);
			return $sError;
		}elseif($this->_sError!==false){
			return '<div id="melding">'.$this->_sMelding.'</div>';
		}else{
			return '';
		}
	}
	function setMelding($sMelding){
		$this->_sMelding=trim($sMelding);
	}
	function invokeRefresh($sMelding, $url=null){
		if($sMelding!=''){
			$_SESSION['melding']=$sMelding;
		}
		if($url==null){
			$url=CSR_ROOT.$_SERVER['REQUEST_URI'];
		}
		header('location: '.$url);
		exit;
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
