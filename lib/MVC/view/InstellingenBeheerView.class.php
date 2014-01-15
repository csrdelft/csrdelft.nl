<?php

/**
 * InstellingenBeheerView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle instellingen om te beheren.
 * 
 */
class InstellingenBeheerView extends TemplateView {

	public function __construct($instellingen) {
		parent::__construct($instellingen);
	}

	public function getTitel() {
		return 'Beheer instellingen stek';
	}

	public function view() {
		if (is_array($this->model)) {
			$this->smarty->assign('melding', $this->getMelding());
			$this->smarty->assign('kop', $this->getTitel());
			$this->smarty->assign('instellingen', $this->model);
			$this->smarty->display('MVC/instellingen/beheer/instellingen_page.tpl');
		} elseif ($this->model instanceof Instelling) {
			$this->smarty->assign('instelling', $this->model);
			$this->smarty->display('MVC/instellingen/beheer/instelling_row.tpl');
		} else { // id of deleted instelling
			echo '<tr id="instelling-row-' . $this->model . '" class="remove"></tr>';
		}
	}

}
