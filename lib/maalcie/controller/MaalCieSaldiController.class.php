<?php

require_once 'lid/saldi.class.php';
require_once 'maalcie/view/MaalCieSaldiView.class.php';
require_once 'maalcie/view/forms/BoekjaarSluitenForm.class.php';

/**
 * MaalCieSaldiController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class MaalCieSaldiController extends AclController {

	public function __construct($query) {
		parent::__construct($query, null);
		if (!$this->isPosted()) {
			$this->acl = array(
				'beheer' => 'P_MAAL_SALDI'
			);
		} else {
			$this->acl = array(
				'upload'		 => 'P_MAAL_SALDI',
				'sluitboekjaar'	 => 'P_MAAL_SALDI'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		parent::performAction();
	}

	public function beheer() {
		$this->view = new MaalCieSaldiView();
		$this->view = new CsrLayoutPage($this->view);
		$this->view->addCompressedResources('maalcie');
	}

	public function upload() {
		$this->beheer();
		Saldi::putMaalcieCsv();
	}

	public function sluitboekjaar() {
		$form = new BoekjaarSluitenForm(date('Y-m-d', strtotime('-1 year')), date('Y-m-d')); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$errors_aantal = MaaltijdenModel::archiveerOudeMaaltijden(strtotime($values['begindatum']), strtotime($values['einddatum']));
			$this->view = new MaalCieSaldiView();
			if (sizeof($errors_aantal[0]) === 0) {
				setMelding('Boekjaar succesvol gesloten: ' . $errors_aantal[1] . ' maaltijden naar het archief verplaatst.', 1);
			}
		} else {
			$this->view = $form;
		}
	}

}
