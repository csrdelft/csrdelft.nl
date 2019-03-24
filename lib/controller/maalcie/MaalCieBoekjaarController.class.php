<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\maalcie\beheer\MaalCieBoekjaarSluitenView;
use CsrDelft\view\maalcie\forms\BoekjaarSluitenForm;

/**
 * MaalCieSaldiController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class MaalCieBoekjaarController extends AclController {

	public function __construct($query) {
		parent::__construct($query, null);
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'beheer' => P_MAAL_SALDI
			);
		} else {
			$this->acl = array(
				'sluitboekjaar' => P_MAAL_SALDI
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
		$this->view = new MaalCieBoekjaarSluitenView();
		$this->view = new CsrLayoutPage($this->view);
	}

	public function sluitboekjaar() {
		$form = new BoekjaarSluitenForm(date('Y-m-d', strtotime('-1 year')), date('Y-m-d')); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$errors_aantal = MaaltijdenModel::instance()->archiveerOudeMaaltijden(strtotime($values['begindatum']), strtotime($values['einddatum']));
			$this->view = new MaalCieBoekjaarSluitenView();
			if (sizeof($errors_aantal[0]) === 0) {
				setMelding('Boekjaar succesvol gesloten: ' . $errors_aantal[1] . ' maaltijden naar het archief verplaatst.', 1);
			}
		} else {
			$this->view = $form;
		}
	}

}
