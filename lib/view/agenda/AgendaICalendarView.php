<?php

namespace CsrDelft\view\agenda;

use CsrDelft\model\agenda\AgendaModel;

class AgendaICalendarView extends AgendaView {

	public function __construct(AgendaModel $agenda) {
		parent::__construct($agenda);
	}

	public function view() {
		$this->smarty->assign('items', $this->model->getICalendarItems());
		$this->smarty->assign('published', str_replace('-', '', str_replace(':', '', str_replace('+00:00', 'Z', gmdate('c')))));
		$this->smarty->display('agenda/icalendar.tpl');
	}

}
