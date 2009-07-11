<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.agendacontent.php
# -------------------------------------------------------------------
# Klasse voor het weergeven van agenda-gerelateerde dingen.
# -------------------------------------------------------------------

require_once('class.agenda.php');

class AgendaContent extends SimpleHTML {
	
	private $agenda;
	private $actie=null;
	
	public function __construct($agenda){
		$this->agenda=$agenda;
	}
	
	public function setActie($actie) {
		$this->actie = $actie;
	}
	
	public function getTitel() {
		$titel = 'Agenda';		
		
		switch ($this->actie) {
			case 'week':
				$titel .= ' - Weekoverzicht';
				break;
		}
		
		return $titel;
	}
	
	public function week() {
		$content = new Smarty_csr();
		$content->display('agenda/week.tpl');
	}
	
	public function maand() {
		$content = new Smarty_csr();
		$content->display('agenda/maand.tpl');
	}
	
	public function view(){
		switch ($this->actie) {
			case 'week':
				$this->week();
				break;

			case 'maand':
				$this->maand();
				break;
		}
	}
}

?>