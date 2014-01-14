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

	private $_instellingen;

	public function __construct($instellingen) {
		parent::__construct();
		$this->_instellingen = $instellingen;
	}

	public function getTitel() {
		return 'Beheer instellingen';
	}

	public function view() {
		if (is_array($this->_instellingen)) {
			$this->assign('melding', $this->getMelding());
			$this->assign('kop', $this->getTitel());

			$this->assign('instellingen', $this->_instellingen);
			$this->display('MVC/instellingen/beheer/instellingen.tpl');
		} elseif (is_string($this->_instellingen)) { // id of deleted corveefunctie
			echo '<tr id="instelling-row-' . $this->_instellingen . '" class="remove"></tr>';
		} else {
			$this->assign('instelling', $this->_instellingen);
			$this->display('MVC/instellingen/beheer/instelling_lijst.tpl');
		}
	}

}

?>