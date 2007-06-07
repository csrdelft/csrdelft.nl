<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.agendacontent.php
# -------------------------------------------------------------------
# Bekijken en wijzigen van maaltijdinschrijving en abonnementen
# -------------------------------------------------------------------


require_once ('class.simplehtml.php');
require_once ('class.lid.php');
require_once ('class.agenda.php');

class AgendaBeheerContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_lid;
	var $_agenda;

	### public ###

	function AgendaBeheerContent (&$lid, &$agenda) {
		$this->_lid =& $lid;
		$this->_agenda =& $agenda;
	}
	function getTitel(){ return 'Agenda'; }

	function viewWaarBenik() {
		echo '<a href="/intern/">Intern</a> &raquo; '.$this->getTitel();
	}
	
	function view(){
	

		if(getOrPost("action") != ''){
			$gelukt = false;
			$action = getOrPost("action");
			if($action == "add"){
				if(isset($_POST['dagid']) && isset($_POST['tijd']) && isset($_POST['tekst'])){
					if($this->_lid->hasPermission('P_AGENDA_POST'){
						$gelukt = $this->_agenda->addAgendaPunt($_POST['dagid'], $_POST['tijd'], $_POST['tekst']);
					}
				}
			}
			elseif($action == "edit"){
				if(isset($_POST['id']) && isset($_POST['datum']) && isset($_POST['tijd']) && isset($_POST['tekst'])){
					if($this->_lid->hasPermission('P_AGENDA_MOD'){
						$gelukt = $this->_agenda->editAgendaPunt($_POST['id'], $_POST['datum'], $_POST['tijd'], $_POST['tekst']);
					}
				}
			}
			elseif($action == "del"){
				if(isset($_POST['id'])){
					if($this->_lid->hasPermission('P_AGENDA_MOD'){
						$gelukt = $this->_agenda->removeAgendaPunt($_POST['id']);
					}
				}
			}
			
			if($gelukt == false){
				echo "Error: de actie is mislukt.";
			}
		}
	
		echo '<h1>Agenda</h1>\n<p>\nOnderstaande is een overzicht van de C.S.R.-agenda voor de aankomende weken.\n';

		$error=$this->_agenda->getError();
		$aAgendaPunten=$this->_agenda->getAgendaPunten($nu, $nu+AGENDA_LIJST_MAX_TOT);
		echo '<table class="agenda">\n';

		$nu = time();
		$midday_time = mktime(12, 0, 0, date("n", $dagiterator), date("j", $dagiterator), date("Y", $dagiterator));
		$week_number_active = '';
	
		for(int i = 0; i<AGENDA_LIJST_MAX_DAGEN; i++){
			$dag = date("D", $midday_time);
			$datum = date("d M", $midday_time);
			$week_number = date("W", $midday_time + 86400);
			$week_number = (int)$week_number;

			if($week_number != $week_number_active){
				$week_number_active = $week_number;
				echo '<tr><td>&nbsp;</td></tr>\n';
				echo '<tr><td colspan="6" class="agenda_week"><strong>Week '.$week_number_active.'</strong></td></tr>\n';
			}
			
			echo '<tr>\n';
			echo '<td class="agenda_toevoegen"><a href="toevoegen/'.$midday_time.'"><img class="button" src="http://plaetjes.csrdelft.nl/documenten/plus.jpg" /></a></td>\n';
			echo '<td class="agenda_dag">'.$dag.'</td>';
			echo '<td class="agenda_datum">'.$datum.'</td>';



			
			$meerdere_activiteiten = false;
			foreach($aAgendaPunten as $agendapunt){
				if(date("w",$midday_time) == date("w",$agendapunt['datum'])){
					if($meerdere_activiteiten){
						echo '<tr>\n';
						echo '<td class="agenda_toevoegen"></td>\n';
						echo '<td class="agenda_dag"></td>';
						echo '<td class="agenda_datum"></td>';
					}
					
					echo '<td class="agenda_buttons">';
					echo '<a href="bewerken/'.$agendapunt['id'].'"><img class="button" src="http://plaetjes.csrdelft.nl/forum/bewerken.png" /></a>';
					echo '<a onclick="return confirm(\'Weet je zeker dat je \\\''.$agendapunt['tekst'].'\\\' wilt verwijderen?\')" href="verwijderen/'.$agendapunt['tekst'].'"><img class="button" src="http://plaetjes.csrdelft.nl/forum/verwijderen.png" /></a>';
					echo '</td>';
					echo '<td class="agenda_tijd">'.$agendapunt['tijd'].'</td>';
					echo '<td class="agenda_activiteit">'.$agendapunt['tekst'].'</td>';
					echo '\n</tr>\n';
					$meerdere_activiteiten = true;
				}
			}

			$midday_time = $midday_time + 86400;
		}
		
		echo '</table>\n'
		echo '</p>\n'
		
	}
}

?>
