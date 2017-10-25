<?php

namespace CsrDelft\view\agenda;

use CsrDelft\model\entity\agenda\Agendeerbaar;

class AgendeerbaarMaandView extends AgendaView {

	public function __construct(Agendeerbaar $item) {
		parent::__construct($item);
	}

	public function view() {
		$this->smarty->assign('item', $this->model);
		$this->smarty->display('agenda/maand_item.tpl');
	}

}
