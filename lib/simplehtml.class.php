<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# simplehtml.class.php
# -------------------------------------------------------------------
# Van deze klasse worden alle klassen afgeleid die ervoor
# bedoeld zijn om uiteindelijk HTML te tonen met view()
# -------------------------------------------------------------------
abstract class SimpleHTML {

	/**
	 * Genereer html
	 */
	public function view() {}
	
	public function getTitel() { return 'C.S.R. Delft'; }
	
	/**
	 * Geeft berichten weer die opgeslagen zijn in de sessie met met setMelding($message, $lvl=0)
     * Levels can be:
	 *
	 * -1 error
	 *  0 info
	 *  1 success
	 *  2 notify
	 *
	 * @return string html van melding(en) of lege string
	 */
	public function getMelding(){
		if(isset($_SESSION['melding']) AND is_array($_SESSION['melding'])){
			$sMelding='<div id="melding">';
			$shown=array();
			foreach($_SESSION['melding'] as $msg){
				$hash = md5($msg['msg']);
				//if(isset($shown[$hash])) continue; // skip double messages
				$sMelding.='<div class="msg'.$msg['lvl'].'">';
				$sMelding.=$msg['msg'];
				$sMelding.='</div>';
				$shown[$hash] = 1;
			}
			$sMelding.='</div>';
			//maar één keer tonen, de melding.
			unset($_SESSION['melding']);
			return $sMelding;
		}else{
			return '';
		}
	}
	public function setMelding($sMelding, $level=-1){
		setMelding($sMelding, $level);
	}
	
	public static function invokeRefresh($url=null, $melding='', $level=-1){
		//als $melding een array is die uit elkaar halen
		if(is_array($melding)){
			list($melding, $level)=$melding;
		}
		if($melding!=''){
			setMelding($melding, $level);
		}
		if($url==null){
			$url=CSR_ROOT.$_SERVER['REQUEST_URI'];
		}
		header('location: '.$url);
		exit;
	}
}
