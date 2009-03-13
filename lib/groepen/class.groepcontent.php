<?php
/*
 * class.groepcontent.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 * Een verzameling contentclassen voor de groepenketzer.
 *
 * Groepcontent					Weergeven van een groep & bewerken en etc.
 * Groepencontent				Weergeven van een groepenoverzicht
 * Groepengeschiedeniscontent	Weergeven van een mooie patchwork van groepjes.
 * GroepenProfielConcent		Weergeven van groepenlijstje in profiel
 * GroepUbbContent				Weergeven van enkele zaken van een groep met een ubb-tag
 */

require_once 'groepen/class.groep.php';
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
		return $_GET['gtype'].' - '.$this->groep->getNaam();
	}

	/*
	 * Deze functie geeft een formulierding voor het eenvoudig toevoegen van leden
	 * aan een bepaalde groep.
	 */
	private function getLidAdder(){
		if(isset($_POST['rawNamen']) AND trim($_POST['rawNamen'])!=''){
			$return='';

			//uitmaken waarin we allemaal zoeken, standaard in de normale leden, wellicht
			//ook in oudleden en nobodies
			$zoekin=array('S_LID', 'S_NOVIET', 'S_GASTLID', 'S_KRINGEL');
			if(isset($_POST['filterOud'])){
				$zoekin[]='S_OUDLID';
			}
			if(isset($_POST['filterNobody']) AND $this->groep->isAdmin()){
				$zoekin[]='S_NOBODY';
			}

			$leden=namen2uid($_POST['rawNamen'], $zoekin);

			if(is_array($leden) AND count($leden)!=0){
				$return.='<table border="0">';

				foreach($leden as $aGroepUid){
					if(isset($aGroepUid['uid'])){
						//naam is gevonden en uniek, dus direct goed.
						$return.='<tr>';
						$return.='<td><input type="hidden" name="naam[]" value="'.$aGroepUid['uid'].'" />'.$aGroepUid['naam'].'</td>';
					}else{
						//naam is niet duidelijk, geef ook een selectievakje met de mogelijke opties
						if(count($aGroepUid['naamOpties'])>0){
							$return.='<tr><td><select name="naam[]" class="tekst">';
							foreach($aGroepUid['naamOpties'] as $aNaamOptie){
								$return.='<option value="'.$aNaamOptie['uid'].'">'.$aNaamOptie['naam'].'</option>';
							}
							$return.='</select></td>';
						}//dingen die niets opleveren wordt niets voor weergegeven.
					}
					if($this->groep->magBewerken()){
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
	/*
	 * Niet-admins kunnen kiezen uit een van te voren vastgesteld lijstje met functies, zodat
	 * we  niet allerlei onzinnamen krijgen zoals Kücherführer enzo.
	 */
	private function getFunctieSelector(){
		$return='';
		$aFuncties=array('Q.Q.', 'Praeses', 'Fiscus', 'Redacteur', 'Computeur', 'Archivaris',
			'Bibliothecaris', 'Statisticus', 'Fotocommissaris','', 'Koemissaris', 'Regisseur',
			'Lichttechnicus', 'Geluidstechnicus', 'Adviseur', 'Internetman', 'Posterman',
			'Corveemanager', 'Provisor', 'HO', 'HJ', 'Onderhuurder');
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
		$content->assign('opvolgerVoorganger', $this->groep->getOpvolgerVoorganger());

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
class Groepgeschiedeniscontent extends SimpleHTML{

	private $groepen;

	public function __construct($groepen){
		$this->groepen=$groepen;
	}
	public function getTitel(){
		return 'Groepen - '.$this->groepen->getNaam();
	}

	public function view(){
		$jaren=5;
		$maanden=$jaren*12;
		echo '<table style="border-collapse: collapse;">';
		echo '<tr>';
		for($i=2008; $i>=(2008-$jaren); $i--){
			echo '<td style="font-size: 8px; width: 10px;" colspan="12">'.$i.'</td>';
		}
		echo '</tr>';
		echo '<tr>';
		for($i=0; $i<=$maanden; $i++){
			echo '<td style="max-width: 10px;">&nbsp;</td>';
		}
		echo '</tr>';
		foreach($this->groepen->getGroepen() as $groep){
			echo '<tr>';
			$startspacer=12-substr($groep->getBegin(), 5,2);
			if($startspacer!=0){
				echo '<td colspan="'.$startspacer.'" style="font-size: 8px; background-color: lightgray;">('.$startspacer.')</td>';
			}

			$oudeGr=Groep::getGroepgeschiedenis($groep->getSnaam(), $jaren);
			foreach($oudeGr as $grp){
				$duration=$grp['duration'];
				if($duration<=0){ $duration=12; }
				echo '<td colspan="'.$duration.'" style="font-size: 8px; border: 1px solid black; padding: 2px; width: 150px; text-align: left;">';
				echo '<a href="/actueel/groepen/'.$this->groepen->getNaam().'/'.$grp['id'].'">'.$grp['naam'].'</a>';

				echo '</td>';
			}
			if(count($oudeGr)<$maanden){
				$spacer=$maanden-count($oudeGr);
				echo '<td colspan="'.$spacer.'" style="background-color: lightgray;">&nbsp;</td>';
			}
			echo '</tr>';
		}
		echo '</table>';

	}
}
class GroepenProfielContent extends SimpleHTML{
	private $uid;
	public function __construct($uid){
		$this->uid=$uid;
	}
	public function getHTML(){
		$return='';

		$aGroepen=Groepen::getGroepenByUid($this->uid);
		if (count($aGroepen) != 0) {
			$currentStatus=null;
			foreach ($aGroepen as $groep) {
				if($currentStatus!=$groep['status']){
					if($currentStatus!=null){
						$return.='</div>';
					}
					$return.='<div class="groep'.$groep['status'].'"><strong>'.str_replace(array('ht','ot', 'ft'), array('h.t.', 'o.t.', 'f.t.'),$groep['status']).' groepen:</strong><br />';
					$currentStatus=$groep['status'];
				}
				$groepnaam=mb_htmlentities($groep['naam']);
				$return.='<a href="/actueel/groepen/'.$groep['gtype'].'/'.$groep['id'].'/">'.$groepnaam."</a><br />\n";
			}
			$return.='</div>';
		}
		return $return;
	}
	public function view(){
		echo $this->getHTML();
	}
}
class GroepUbbContent extends SimpleHTML{
	private $groep;
	private $style;
	public function __construct($groepid, $style='default'){
		$this->groep=new Groep((int)$groepid);
	}
	public function getHTML(){
		$content=new Smarty_csr();
		$content->assign('groep', $this->groep);
		return $content->fetch('groepen/groep.ubb.tpl');
	}
	public function view(){
		echo $this->getHTML();
	}
}
class GroepStatsContent extends SimpleHTML{
	private $groep;

	public function __construct($groep){
		$this->groep=$groep;
	}
	public function view(){
		$stats=$this->groep->getStats();
		foreach($stats as $title => $stat){
			echo '<table class="query_table">';
			$rowColor=false;
			foreach($stat as $row){
				//kleurtjes omwisselen
				if($rowColor){
					$style='style="background-color: #ccc;"';
				}else{
					$style='';
				}
				$rowColor=(!$rowColor);
				echo '<tr>';
				foreach($row as $column){
					echo '<td '.$style.'>'.$column.'</td>';
				}
				echo '</tr>';
			}
			echo '</table><br />';
		}
	}

}
?>