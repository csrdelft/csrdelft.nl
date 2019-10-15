<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\model\entity\maalcie\CorveeVoorkeur;
use CsrDelft\model\maalcie\CorveeVoorkeurenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\maalcie\forms\EetwensForm;
use CsrDelft\view\maalcie\persoonlijk\voorkeuren\MijnVoorkeurenView;
use CsrDelft\view\maalcie\persoonlijk\voorkeuren\MijnVoorkeurView;


/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MijnVoorkeurenController {
	private $model;

	public function __construct() {
		$this->model = CorveeVoorkeurenModel::instance();
	}

	public function mijn() {
		$voorkeuren = $this->model->getVoorkeurenVoorLid(LoginModel::getUid(), true);
		$view = new MijnVoorkeurenView($voorkeuren);
		return view('default', ['content' => $view]);
	}

	public function inschakelen($crid) {
		$voorkeur = new CorveeVoorkeur();
		$voorkeur->crv_repetitie_id = $crid;
		$voorkeur->uid = LoginModel::getUid();
		$voorkeur = $this->model->inschakelenVoorkeur($voorkeur);
		return new MijnVoorkeurView($voorkeur);
	}

	public function uitschakelen($crid) {
		$voorkeur = new CorveeVoorkeur();
		$voorkeur->crv_repetitie_id = $crid;
		$voorkeur->uid = LoginModel::getUid();
		$voorkeur = $this->model->uitschakelenVoorkeur($voorkeur);
		return new MijnVoorkeurView($voorkeur);
	}

	public function eetwens() {
		$form = new EetwensForm();
		if ($form->validate()) {
			$this->model->setEetwens(LoginModel::getProfiel(), $form->getField()->getValue());
		}
		return $form;
	}

}
