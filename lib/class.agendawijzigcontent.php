<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.agendatoevoegcontent.php
# -------------------------------------------------------------------
# Wijzigen van agendapunten
# -------------------------------------------------------------------


require_once ('class.simplehtml.php');
require_once ('class.lid.php');
require_once ('class.agenda.php');

class AgendaWijzigContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_lid;
	var $_agenda;

	### public ###

	function AgendaWijzigContent (&$lid, &$agenda) {
		$this->_lid =& $lid;
		$this->_agenda =& $agenda;
	}
	function getTitel(){ return 'Agendapunt wijzigen'; }

	function viewWaarBenik() {
		echo '<a href="/intern/">Intern</a> &raquo; '.$this->getTitel();
	}

	function view(){

		if(getOrPost("mode") == '' || getOrPost("id") == ''){
			echo 'Er is een fout opgetreden bij het verkrijgen van de gegevens. <a href="#" onClick="history.go(-1);">Ga terug</a> en probeer het opnieuw.';
		}
		else{
			$agendapunt = $this->_agenda->getAgendaPunt(getOrPost("id"));
			$error=$this->_agenda->getError();
			echo '<h1>Agendapunt toevoegen</h1>\n
					<p>\n
					Voer een begintijdstip en een omschrijving in, om het agendapunt toe te voegen.
					';
			// begin form
			echo '<form enctype="multipart/form-data" action="." method="POST">'."\n";
			echo '<input type="hidden" name="action" value="'.getOrPost("mode").'">'."\n";
			echo '<input type="hidden" name="id" value="'.getOrPost("id").'">'."\n";
			echo '<input type="hidden" name="datum" value="'.getOrPost("datum").'">'."\n";
			
			// table
			echo '<table border="0" class="forumtabel">'."\n";

			echo '<tr>';
			echo '<td>Begint om:</td>';
			echo '<td><input name="tijd" type="text" value="'.$agendapunt['tijd'].'"/></td>';
			echo '</tr>';
			echo '<tr><td>Omschrijving:</td>';
			echo '<td><input name="tekst" type="text" value="'.$agendapunt['tekst'].'"/></td>';
			echo '</tr>'."\n";
			echo '</table>'."\n";
			echo '<span><input type="submit" value="Wijzigen" /></span>'."\n";
			echo '<span><input type="button" value="Annuleren" onClick="window.location=\'../\'" /></span>'."\n";
			echo '</form>'."\n";
		}
	}
}

?>
