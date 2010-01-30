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
		$filter = !LoginLid::instance()->hasPermission('P_AGENDA_MOD');
		
		$content = new Smarty_csr();
		$content->assign('datum', strtotime($this->jaar.'-'.$this->maand.'-01'));
		$content->assign('weken', $this->agenda->getItemsByMaand($this->jaar, $this->maand, $filter));
		$content->assign('magToevoegen', $this->agenda->magToevoegen());
		$content->assign('magBeheren', $this->agenda->magBeheren());
		$content->assign('melding', $this->getMelding());

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

class AgendaItemContent extends SimpleHTML {

	private $agenda;
	private $item;
	private $actie;

	public function __construct($agenda, $item, $actie) {
		$this->agenda = $agenda;
		$this->item = $item;
		$this->actie = $actie;
	}
	
	public function getTitel() {
		return 'Agenda - Item toevoegen';
	}
	
	public function view() {
		$content = new Smarty_csr();
		$content->assign('item', $this->item);
		$content->assign('actie', $this->actie);
		$content->assign('melding', $this->getMelding());
		$content->display('agenda/item.tpl');
	}
}

class AgendaZijbalkContent extends SimpleHTML {
	
	private $agenda;
	private $aantalWeken;
	
	public function __construct($agenda, $aantalWeken) {
		$this->agenda = $agenda;
		$this->aantalWeken = $aantalWeken;
	}
	
	public function getTitel() {
		return 'Agenda - Zijbalk';
	}
	
	public function view() {
		$filter = !LoginLid::instance()->hasPermission('P_AGENDA_MOD');
		
		$beginMoment = strtotime(date('Y-m-d'));
		$eindMoment = strtotime('+'.$this->aantalWeken.' weeks', $beginMoment);
		$eindMoment = strtotime('next saturday', $eindMoment);
		$items = $this->agenda->getItems($beginMoment, $eindMoment, $filter);
		
		if(count($items)>Instelling::get('zijbalk_agenda_max')){
			$items=array_slice($items, Instelling::get('zijbalk_agenda_max'));
		}
		
		$content = new Smarty_csr();
		$content->assign('items', $items);
		$content->display('agenda/zijbalk.tpl');
	}
}
class AgendaCourantContent extends SimpleHTML{
	private $agenda;
	private $aantalWeken;
	
	public function __construct($agenda, $aantalWeken) {
		$this->agenda = $agenda;
		$this->aantalWeken = $aantalWeken;
		
		$filter = !LoginLid::instance()->hasPermission('P_AGENDA_MOD');
		
		$beginMoment = strtotime(date('Y-m-d'));
		$eindMoment = strtotime('+'.$this->aantalWeken.' weeks', $beginMoment);
		$eindMoment = strtotime('next saturday', $eindMoment);
		$items = $this->agenda->getItems($beginMoment, $eindMoment, $filter);
		
		$content = new Smarty_csr();
		$content->assign('items', $items);
		$content->display('agenda/courant.tpl');
	}
	
}

class AgendaIcalendarContent extends SimpleHTML {
	
	private $agenda;
	
	public function __construct($agenda) {
		$this->agenda = $agenda;
	}
	
	public function view() {
		$filter = !LoginLid::instance()->hasPermission('P_AGENDA_MOD');
		$items = $this->agenda->getItems(null, null, $filter);
		
		$content = new Smarty_csr();
		$content->assign('items', $items);
		$content->display('agenda/icalendar.tpl');
	}
}

?>