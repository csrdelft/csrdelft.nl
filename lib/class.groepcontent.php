<?php
/*
 * class.groepcontent.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 * 
 * 
 */


class Groepcontent extends SimpleHTML{
	
	private $groep;
	private $action='view';
	
	public function __construct($groep){
		$this->groep=$groep;
	}
	public function setAction($action){
		$this->action=$action;
	}
	public function getTitel(){
		return ''.$_GET['gtype'].' - '.$this->groep->getNaam();
	}
	private function getLidAdder(){
		if(isset($_POST['rawNamen']) AND trim($_POST['rawNamen'])!=''){
			$return='';
			$aCieUids=namen2uid($_POST['rawNamen']);
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
					if($this->groep->isAdmin()){
						$return.='<td><input type="text" name="functie[]" /></td></tr>';
					}else{
						$return.='<td>'.$this->getFunctieSelector().'</td></tr>';
					}
				}
				$return.='</table>';
				return $return;
			}
		}
		return false;
	} 
	private function getFunctieSelector(){
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
	public function view(){
		$content=new Smarty_csr();
		
		$content->assign('groep', $this->groep);
		
		$content->assign('action', $this->action);
		$content->assign('gtype', $_GET['gtype']);
		$content->assign('groeptypes', Groepen::getGroeptypes());
		
		if($this->action=='addLid'){
			$content->assign('lidAdder', $this->getLidAdder());
		}
		
		$content->assign('melding', $this->getMelding());
		$content->display('groepen/groep.tpl');		
	}
}
class Groepencontent extends SimpleHTML{
	
	private $groepen;
	private $action='view';
	
	public function __construct($groepen){
		$this->groepen=$groepen;
	}
	public function setAction($action){
		$this->action=$action;
	}
	public function getTitel(){
		return 'Groepen - '.$this->groepen->getNaam();
	}
	
	public function view(){
		$content=new Smarty_csr();
		
		$content->assign('groepen', $this->groepen);
		
		$content->assign('action', $this->action);
		$content->assign('gtype', $this->groepen->getNaam());
		$content->assign('groeptypes', Groepen::getGroeptypes());
		
		$content->assign('melding', $this->getMelding());
		$content->display('groepen/groepen.tpl');		
		
	}
}
?>
