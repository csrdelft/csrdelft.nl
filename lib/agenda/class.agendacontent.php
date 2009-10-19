<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.agendacontent.php
# -------------------------------------------------------------------
# Klasse voor het weergeven van agenda-gerelateerde dingen.
# -------------------------------------------------------------------

require_once('class.agenda.php');

class AgendaMaandContent extends SimpleHTML {
	
	private $agenda;
	private $jaar;
	private $maand;
	
	public function __construct($agenda, $jaar, $maand){
		$this->agenda=$agenda;
		$this->jaar=$jaar;
		$this->maand=$maand;
	}
		
	public function getTitel() {
		$titel = 'Agenda - Maandoverzicht voor '.strftime('%B %Y', strtotime($this->jaar.'-'.$this->maand.'-01'));		
				
		return $titel;
	}
	
	public function view(){
		$content = new Smarty_csr();
		$content->assign('datum', strtotime($this->jaar.'-'.$this->maand.'-01'));
		$content->assign('weken', $this->agenda->getItemsByMaand($this->jaar, $this->maand));
		$content->assign('magToevoegen', $this->agenda->magToevoegen());
		
		// URL voor vorige maand
		$urlVorige = CSR_ROOT.'actueel/agenda/';
		if ($this->maand == 1) {
			$urlVorige .= ($this->jaar-1).'-12/';
		} else {
			$urlVorige .= $this->jaar.'-'.($this->maand-1).'/';
		}	
		$content->assign('urlVorige', $urlVorige);
		
		// URL voor volgende maand
		$urlVolgende = CSR_ROOT.'actueel/agenda/';
		if ($this->maand == 12) {
			$urlVolgende .= ($this->jaar+1).'-1/';
		} else {
			$urlVolgende .= $this->jaar.'-'.($this->maand+1).'/';
		}	
		$content->assign('urlVolgende', $urlVolgende);		
		
		$content->display('agenda/maand.tpl');
	}
}

?>