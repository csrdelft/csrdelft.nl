<?php
# C.S.R. Delft
# -------------------------------------------------------------------
# class.commissiecontent.php
# -------------------------------------------------------------------
# Beeldt informatie af over Commissies
# -------------------------------------------------------------------


require_once ('class.simplehtml.php');
require_once ('class.commissie.php');

class CommissieContent extends SimpleHTML {

	private $_commissie;
	private $action='view';

	function CommissieContent($commissie) {
		$this->_commissie=$commissie;
	}
	function getTitel(){
		if(preg_match('/^\d+$/', $_GET['cie'])){
			return 'Commissies - '.$this->_commissie->getNaam();
		}else{
			return 'Commissies - '.mb_htmlentities($_GET['cie']);
		}
	}
	function setAction($action){
		$this->action=$action;
	}
	function viewWaarbenik(){
		echo '<a href="/groepen/">Groepen</a> &raquo; <a href="/groepen/commissies.php">Commissies</a> &raquo; '.$this->getTitel();
	}
	
	private function getLidAdder(){
		if(isset($_POST['cieNamen']) AND trim($_POST['cieNamen'])!=''){
			$return='';
			$aCieUids=namen2uid($_POST['cieNamen']);
			if(is_array($aCieUids) AND count($aCieUids)!=0){
				$return.='<table border="0">';
				$return.='<tr><th>Naam</hd><th>Functie</th></tr>';
				
				foreach($aCieUids as $aCieUid){
					if(isset($aCieUid['uid'])){
						//naam is gevonden en uniek, dus direct goed.
						$return.='<tr>';
						$return.='<td><input type="hidden" name="naam[]" value="'.$aCieUid['uid'].'" />'.$aCieUid['naam'].'</td>';
					}else{
						//naam is niet duidelijk, geef ook een selectievakje met de mogelijke opties
						if(count($aCieUid['naamOpties'])>0){
							$return.='<tr><td><select name="naam[]" class="tekst">';
							foreach($aCieUid['naamOpties'] as $aNaamOptie){
								$return.='<option value="'.$aNaamOptie['uid'].'">'.$aNaamOptie['naam'].'</option>';
							}
							$return.='</select></td>';
						}//dingen die niets opleveren wordt niets voor weergegeven.
					}
					$return.='<td>'.$this->_getFunctieSelector().'</td></tr>';
				}
				$return.='</table>';
				return $return;
			}
		}
		return false;
	} 
	function viewCommissie($cie){
		$ciecontent=new Smarty_csr();
		
		$ciecontent->assign('cie', $this->_commissie->getCommissie());
		
		$ciecontent->assign('aCieLeden', Commissie::getLeden($cie['id']));
		$ciecontent->assign('magBewerken', $this->_commissie->magBewerken());
		
		$ciecontent->assign('action', $this->action);
		$ciecontent->assign('lidAdder', $this->getLidAdder());
		
		$ciecontent->assign('melding', $this->getMelding());
		$ciecontent->display('commissie.tpl');		
	}
	function _getFunctieSelector(){
		$return='';
		$aFuncties=array('Q.Q.', 'Praeses', 'Fiscus', 'Redacteur', 'Computeur', 'Archivaris', 
			'Bibliothecaris', 'Statisticus', 'Fotocommissaris','', 'Koemissaris', 'Regisseur', 
			'Lichttechnicus', 'Geluidstechnicus', 'Adviseur', 'Internetman', 'Posterman', 
			'Corveemanager', 'Provisor');
		sort($aFuncties);
		$return.='<select name="functie[]" class="tekst">';
		foreach($aFuncties as $sFunctie){
			$return.='<option value="'.$sFunctie.'">'.$sFunctie.'</option>';
		}
		$return.='</select>';
		return $return;
	}
		
	function view() {
		$cie = $this->_commissie->getCommissie();
		echo $this->viewCommissie($cie);
	}
}

?>
