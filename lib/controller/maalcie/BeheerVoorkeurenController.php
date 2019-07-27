<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\controller\framework\AclController;
use CsrDelft\model\entity\maalcie\CorveeVoorkeur;
use CsrDelft\model\maalcie\CorveeVoorkeurenModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\maalcie\corvee\voorkeuren\BeheerVoorkeurenView;
use CsrDelft\view\maalcie\corvee\voorkeuren\BeheerVoorkeurView;

/**
 * BeheerVoorkeurenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @property CorveeVoorkeurenModel $model
 *
 */
class BeheerVoorkeurenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, CorveeVoorkeurenModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'beheer' => P_CORVEE_MOD
			);
		} else {
			$this->acl = array(
				'inschakelen' => P_CORVEE_MOD,
				'uitschakelen' => P_CORVEE_MOD
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		parent::performAction($this->getParams(3));
	}

	public function beheer() {
		$matrix_repetities = $this->model->getVoorkeurenMatrix();
		$this->view = new BeheerVoorkeurenView($matrix_repetities[0], $matrix_repetities[1]);
		$this->view = new CsrLayoutPage($this->view);
	}

	public function inschakelen($crid, $uid) {
		if (!ProfielModel::existsUid($uid)) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}
		$voorkeur = new CorveeVoorkeur();
		$voorkeur->crv_repetitie_id = $crid;
		$voorkeur->uid = $uid;

		$voorkeur = $this->model->inschakelenVoorkeur($voorkeur);
		$voorkeur->setVanUid($voorkeur->getUid());
		$this->view = new BeheerVoorkeurView($voorkeur);
	}

	public function uitschakelen($crid, $uid) {
		if (!ProfielModel::existsUid($uid)) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}
		$voorkeur = new CorveeVoorkeur();
		$voorkeur->crv_repetitie_id = (int)$crid;
		$voorkeur->uid = $uid;
		$voorkeur->setVanUid($uid);

		$this->model->uitschakelenVoorkeur($voorkeur);

		$voorkeur->uid = null;
		$this->view = new BeheerVoorkeurView($voorkeur);
	}

}
