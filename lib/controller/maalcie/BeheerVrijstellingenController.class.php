<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\controller\framework\AclController;
use CsrDelft\model\entity\maalcie\CorveeVrijstelling;
use CsrDelft\model\maalcie\CorveeVrijstellingenModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\maalcie\corvee\vrijstellingen\BeheerVrijstellingenView;
use CsrDelft\view\maalcie\corvee\vrijstellingen\BeheerVrijstellingView;
use CsrDelft\view\maalcie\forms\VrijstellingForm;


/**
 * BeheerVrijstellingenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @property CorveeVrijstellingenModel $model
 *
 */
class BeheerVrijstellingenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, CorveeVrijstellingenModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'beheer' => P_CORVEE_MOD
			);
		} else {
			$this->acl = array(
				'nieuw' => P_CORVEE_MOD,
				'bewerk' => P_CORVEE_MOD,
				'opslaan' => P_CORVEE_MOD,
				'verwijder' => P_CORVEE_MOD
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$uid = null;
		if ($this->hasParam(3)) {
			$uid = $this->getParam(3);
		}
		parent::performAction(array($uid));
	}

	public function beheer() {
		/** @var CorveeVrijstelling[] $vrijstellingen */
		$vrijstellingen = $this->model->find();
		$this->view = new BeheerVrijstellingenView($vrijstellingen);
		$this->view = new CsrLayoutPage($this->view);
	}

	public function nieuw() {
		$vrijstelling = $this->model->nieuw();
		$this->view = new VrijstellingForm($vrijstelling); // fetches POST values itself
	}

	public function bewerk($uid) {
		if (!ProfielModel::existsUid($uid)) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}
		$vrijstelling = $this->model->getVrijstelling($uid);
		$this->view = new VrijstellingForm($vrijstelling); // fetches POST values itself
	}

	public function opslaan($uid = null) {
		if ($uid !== null) {
			$this->bewerk($uid);
		} else {
			$this->nieuw();
		}
		if ($this->view->validate()) {
			$values = $this->view->getValues();
			$vrijstelling = $this->model->saveVrijstelling($values['uid'], $values['begin_datum'], $values['eind_datum'], $values['percentage']);
			$this->view = new BeheerVrijstellingView($vrijstelling);
		}
	}

	public function verwijder($uid) {
		if (!ProfielModel::existsUid($uid)) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}
		$this->model->verwijderVrijstelling($uid);
		echo '<tr id="vrijstelling-row-' . $uid . '" class="remove"></tr>';
		exit;
	}

}
