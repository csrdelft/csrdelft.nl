<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.agendacontent.php
# -------------------------------------------------------------------
# Bekijken en wijzigen van agendapunten
# -------------------------------------------------------------------


require_once ('class.simplehtml.php');
require_once ('class.lid.php');
require_once ('class.agenda.php');

class AgendaContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_lid;
	var $_agenda;

	### public ###

	function AgendaContent (&$lid, &$agenda) {
		$this->_lid =& $lid;
		$this->_agenda =& $agenda;
	}
	function getTitel(){ return 'Agenda'; }

	function viewWaarBenik() {
		echo '<a href="/intern/">Intern</a> &raquo; '.$this->getTitel();
	}
	
	function magBeheren(){
		return $this->_lid->hasPermission('P_AGENDA_POST');
	}
	
	function view(){

		echo '<h1>Agenda</h1><p>Onderstaande is een overzicht van de C.S.R.-agenda voor de aankomende weken.';

		$now = time();
		$this_day = mktime(0, 0, 0, date("n", $now), date("j", $now), date("Y", $now));
		$week_number_active = '';

		$error=$this->_agenda->getError();
		$aAgendaPunten=$this->_agenda->getAgendaPunten($now, $now+ 86400*AGENDA_LIJST_DEFAULT_DAGEN);
		echo '<table class="agenda">';
	
		for($i = 0; $i<AGENDA_LIJST_DEFAULT_DAGEN; $i++){
			$datum = date("D d M", $this_day);
			$week_number = date("W", strtotime("+1 day", $this_day));

			if($week_number != $week_number_active){
				$week_number_active = $week_number;
				echo '<tr><td>&nbsp;</td></tr>';
				echo '<tr><td ';
				if($this->magBeheren()){
					echo 'colspan="5"';
				}
				else{
					echo 'colspan="3"';
				}
				echo ' class="agenda_week"><strong>Week '.$week_number_active.'</strong></td></tr>';
			}
			
			echo '<tr>';
			if($this->magBeheren()){
				echo '<td class="agenda_toevoegen"><a href="toevoegen/'.$this_day.'"><img class="button" src="http://plaetjes.csrdelft.nl/documenten/plus.jpg" /></a></td>';
			}
			echo '<td class="agenda_datum">'.$datum.'</td>';


			$meerdere_activiteiten = false;
			foreach($aAgendaPunten as $agendapunt){
				if(date("Y z",$this_day) == date("Y z",$agendapunt['tijd'])){
					if($meerdere_activiteiten){
						echo '</tr><tr>';
						if($this->magBeheren()){
							echo '<td class="agenda_toevoegen"></td>';
						}
						echo '<td class="agenda_datum"></td>';
					}
					
					if($this->magBeheren()){
						echo '<td class="agenda_buttons">';
						echo '<a href="bewerken/'.$agendapunt['id'].'"><img class="button" src="http://plaetjes.csrdelft.nl/forum/bewerken.png" /></a>';
						echo '<a onclick="return confirm(\'Weet je zeker dat je \\\''.$agendapunt['tekst'].'\\\' wilt verwijderen?\')" href="index.php?id='.$agendapunt['id'].'&action=del"><img class="button" src="http://plaetjes.csrdelft.nl/forum/verwijderen.png" /></a>';
						echo '</td>';
					}
					echo '<td class="agenda_tijd">';
					$tijd = date("H:i", $agendapunt['tijd']);
					if($tijd == '00:00'){
						echo 'n.v.t.';
					}
					else{
						echo $tijd;
					}
					echo '</td>';
					echo '<td class="agenda_activiteit">'.$agendapunt['tekst'].'</td>';
					$meerdere_activiteiten = true;
				}
			}
			if(!$meerdere_activiteiten){
				if($this->magBeheren()){
					echo '<td class="agenda_buttons"></td>';
				}
				echo '<td class="agenda_tijd"></td>';
				echo '<td class="agenda_activiteit"></td>';
			}
			echo '</tr>';

			$this_day = strtotime("+1 day", $this_day);
		}
		
		echo '</table>';
		echo '</p>';
		
	}
}

?>
