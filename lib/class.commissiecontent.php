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

	### private ###

	# de objecten die data leveren
	var $_commissie;
	var $_lid;

	### public ###

	function CommissieContent (&$commissie, &$lid) {
		$this->_commissie =& $commissie;
		$this->_lid =& $lid;
	}
	function getTitel(){
		if(preg_match('/^\d+$/', $_GET['cie'])){
			return 'Commissies - '.$this->_commissie->getNaam($_GET['cie']);
		}else{
			return 'Commissies - '.mb_htmlentities($_GET['cie']);
		}
	}
	function viewWaarbenik(){
		echo '<a href="/groepen/">Groepen</a> &raquo; 
					<a href="/groepen/commissies.php">Commissies</a> &raquo; 
					'.$this->getTitel();
	}
	function viewCommissie($cie){
		$ubb = new csrUbb();
		$sTekst=$ubb->getHTML($cie['tekst']);
		
		echo '<table>
			<tr><td><h2>'.$cie['titel'].'</h2></td><td width="250px">&nbsp;</td></tr>
			<tr><td>'.$sTekst;
		//eventueel link
		if ($cie['link'] != '') {
			echo 'CommissieWebstek: <a href="'.htmlspecialchars($cie['link']).'">'.mb_htmlentities($cie['link']).'</a>';	
		}
		echo '</td><td valign="top">';
		$aCieLeden=$this->_commissie->getCieLeden($cie['id']);
		if(is_array($aCieLeden)){
			echo '<table border="0"  class="hoktable" ><tr><th colspan="2">Commissieleden:</th></tr>';
			foreach($aCieLeden as $aCieLid){
				echo '<tr><td width="150px">'.$this->_lid->getNaamLink($aCieLid['uid'], 'civitas', true, $aCieLid).'</td><td>'.mb_htmlentities($aCieLid['functie']);
				echo '</td>';
				if($this->_commissie->magBewerken()){
					echo '<td><a href="/groepen/commissie/'.$cie['id'].'/verwijder/lid/'.$aCieLid['uid'].'">X</a></td>';
				}
				
				echo '</tr>';
				
			}
			if($this->_commissie->magBewerken()){ 
				echo '<tr><td colspan="2">Som van SocCie-saldo: &euro; '.sprintf ("%01.2f", $this->_commissie->getCieSaldo()).'</td></tr>'; 
			}
			echo '</table>';
		}else{
			if($aCieLeden!==false){
				echo '<table border="0" cellpadding="5px" class="hoktable" ><tr><td>'.$aCieLeden.'</td></tr></table>';
			}
		}
		echo '</td></tr></table>';
		if($this->_commissie->magBewerken()){
			echo '<hr /><h2>Deze commissie beheren:</h2>
				<br />
				<form action="/groepen/commissie/'.$cie['id'].'" method="post">';
			$tekstInvoer=true;
			if(isset($_POST['cieNamen']) AND trim($_POST['cieNamen'])!=''){
				$aCieUids=namen2uid($_POST['cieNamen'], $this->_lid);
				if(is_array($aCieUids) AND count($aCieUids)!=0){
					echo '<table border="0">';
					echo '<tr><th>Naam</hd><th>Functie</th></tr>';
					
					foreach($aCieUids as $aCieUid){
						if(isset($aCieUid['uid'])){
							//naam is gevonden en uniek, dus direct goed.
							echo '<tr>';
							echo '<td><input type="hidden" name="naam[]" value="'.$aCieUid['uid'].'" />'.$aCieUid['naam'].'</td>';
						}else{
							//naam is niet duidelijk, geef ook een selectievakje met de mogelijke opties
							if(count($aCieUid['naamOpties'])>0){
								echo '<tr><td><select name="naam[]" class="tekst">';
								foreach($aCieUid['naamOpties'] as $aNaamOptie){
									echo '<option value="'.$aNaamOptie['uid'].'">'.$aNaamOptie['naam'].'</option>';
								}
								echo '</select></td>';
							}//dingen die niets opleveren wordt niets voor weergegeven.
						}
						echo '<td>'.$this->_getFunctieSelector().'</td></tr>';
					}
					echo '</table>';
					$tekstInvoer=false;
				}
			}
			if($tekstInvoer){
				echo 'Geef hier namen of lidnummers op voor deze commissie, gescheiden door komma\'s<br />
					<input type="text" name="cieNamen" class="tekst" />';
			}
			echo '<input type="submit" value="Verzenden" /></form>';
		}
		
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
