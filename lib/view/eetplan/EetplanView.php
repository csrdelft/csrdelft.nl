<?php

namespace CsrDelft\view\eetplan;

class EetplanView extends AbstractEetplanView {
	function view() {
		$eetplantable = new EetplanTableView($this->model->getEetplan($this->lichting));
		$this->smarty->assign('table', $eetplantable);
		$this->smarty->assign('avonden', $this->model->getAvonden($this->lichting));
		$this->smarty->assign('eetplan', $this->model->getEetplan($this->lichting));
		$this->smarty->display('eetplan/overzicht.tpl');
	}
}
