<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.agendatoevoegcontent.php
# -------------------------------------------------------------------
# Toevoegen van agendapunten
# -------------------------------------------------------------------


require_once ('class.simplehtml.php');
require_once ('class.lid.php');
require_once ('class.agenda.php');

class AgendaToevoegContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_lid;
	var $_agenda;

	### public ###

	function AgendaToevoegContent (&$lid, &$agenda) {
		$this->_lid =& $lid;
		$this->_agenda =& $agenda;
	}
	function getTitel(){ return 'Agendapunt toevoegen'; }

	function viewWaarBenik() {
		echo '<a href="/intern/">Intern</a> &raquo; '.$this->getTitel();
	}

	function view(){

		$error=$this->_agenda->getError();

		if(getOrPost("mode") == '' || getOrPost("dagid") == ''){
			echo 'Er is een fout opgetreden bij het verkrijgen van de gegevens. <a href="#" onClick="history.go(-1);">Ga terug</a> en probeer het opnieuw.';
		}
		else{
			echo '<h1>Agendapunt toevoegen</h1>
					<p>
					Voer een begintijdstip en een omschrijving in, om het agendapunt toe te voegen.
					';
			// begin form
			echo '<form enctype="multipart/form-data" action="../" method="POST">'."\n";
			echo '<input type="hidden" name="action" value="'.getOrPost("mode").'">'."\n";
			echo '<input type="hidden" name="dagid" value="'.getOrPost("dagid").'">'."\n";
			
			// table
			echo '<table border="0" class="forumtabel">'."\n";

			echo '<tr>';
			echo '<td>Begint om:</td>';
			echo '<td><input name="tijd" type="text" value=""/></td>';
			echo '</tr>';
			echo '<tr><td>Omschrijving:</td>';
			echo '<td><input name="tekst" type="text" value=""/></td>';
			echo '</tr>'."\n";
			echo '</table>'."\n";
			echo '<span><input type="submit" value="Toevoegen" /></span>'."\n";
			echo '<span><input type="button" value="Annuleren" onClick="window.location=\'../\'" /></span>'."\n";
			echo '</form>'."\n";
		}
	}
}

?>
