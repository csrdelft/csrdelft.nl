<?php

/**
 * MijnVoorkeurenView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van voorkeuren die een lid aan of uit kan zetten.
 * 
 */
class MijnVoorkeurenView extends TemplateView {

	private $_voorkeuren;
	private $_eetwens;

	public function __construct($voorkeuren = null, $eetwens = null) {
		parent::__construct();
		$this->_voorkeuren = $voorkeuren;

		$fields[] = new AutoresizeTextareaField('eetwens', $eetwens, 'Allergie/diëet:');
		$this->_eetwens = new InlineForm('eetwens-form', Instellingen::get('taken', 'url') . '/eetwens', $fields);
	}

	public function getTitel() {
		return 'Mijn voorkeuren';
	}

	public function view() {
		if ($this->_voorkeuren === null) { // eetwens
			echo $this->_eetwens->view(true);
		}
		elseif (is_array($this->_voorkeuren)) { // list of voorkeuren
			$this->smarty->display('taken/menu_pagina.tpl');

			$this->smarty->assign('eetwens', $this->_eetwens);
			$this->smarty->assign('voorkeuren', $this->_voorkeuren);
			$this->smarty->display('taken/voorkeur/mijn_voorkeuren.tpl');
		}
		elseif (is_int($this->_voorkeuren)) { // id of disabled voorkeur
			$this->smarty->assign('crid', $this->_voorkeuren);
			$this->smarty->display('taken/voorkeur/mijn_voorkeur_veld.tpl');
		}
		else { // single voorkeur
			$this->smarty->assign('uid', $this->_voorkeuren->getLidId());
			$this->smarty->assign('crid', $this->_voorkeuren->getCorveeRepetitieId());
			$this->smarty->display('taken/voorkeur/mijn_voorkeur_veld.tpl');
		}
	}

}
