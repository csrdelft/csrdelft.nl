<?php

namespace CsrDelft\view\eetplan;

class EetplanBeheerView extends AbstractEetplanView {
	public function getTitel() {
		return 'Eetplanbeheer';
	}

	public function getBreadcrumbs() {
		return parent::getBreadcrumbs() . ' » <span>Beheer</span>';
	}

	public function view() {
		$this->smarty->assign("bekendentable", new EetplanBekendenTable());
		$this->smarty->assign("huizentable", new EetplanHuizenTable()); // TODO: consistentie huizen-woonoorden
		$this->smarty->assign("bekendehuizentable", new EetplanBekendeHuizenTable());
		$this->smarty->assign("table", new EetplanTableView($this->model));
		$this->smarty->display('eetplan/beheer.tpl');
	}
}
