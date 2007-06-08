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
	
	function view(){
	
		echo '<h1>Agenda</h1><p>Onderstaande is een overzicht van de C.S.R.-agenda voor de aankomende weken.';

		$dagiterator = time();
		$midday_time = mktime(12, 0, 0, date("n", $dagiterator), date("j", $dagiterator), date("Y", $dagiterator));
		$week_number_active = '';

                $error=$this->_agenda->getError();
                $aAgendaPunten=$this->_agenda->getAgendaPunten($dagiterator, $dagiterator+AGENDA_LIJST_MAX_TOT);
		echo '<table class="agenda">';
	
		for($i = 0; $i<AGENDA_LIJST_MAX_DAGEN; $i++){
			$dag = date("D", $midday_time);
			$datum = date("d M", $midday_time);
			$week_number = date("W", $midday_time + 86400);
			$week_number = (int)$week_number;

			if($week_number != $week_number_active){
				$week_number_active = $week_number;
				echo '<tr><td>&nbsp;</td></tr>';
				echo '<tr><td colspan="4" class="agenda_week"><strong>Week '.$week_number_active.'</strong></td></tr>';
			}
			
			echo '<tr>';
			echo '<td class="agenda_dag">'.$dag.'</td>';
			echo '<td class="agenda_datum">'.$datum.'</td>';


			$meerdere_activiteiten = false;
			foreach($aAgendaPunten as $agendapunt){
				if(date("Y z",$midday_time) == date("Y z",$agendapunt['datum'])){
					if($meerdere_activiteiten){
						echo '</tr><tr>';
						echo '<td class="agenda_dag"></td>';
						echo '<td class="agenda_datum"></td>';
					}
					
					echo '<td class="agenda_tijd">'.$agendapunt['tijd'].'</td>';
					echo '<td class="agenda_activiteit">'.$agendapunt['tekst'].'</td>';
					$meerdere_activiteiten = true;
				}
			}
			if(!$meerdere_activiteiten){
				echo '<td class="agenda_tijd"></td>';
				echo '<td class="agenda_activiteit"></td>';
			}
			echo '</tr>';

			$midday_time = $midday_time + 86400;
		}
		
		echo '</table>';
		echo '</p>';
		
	}
}

?>
