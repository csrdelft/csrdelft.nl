<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\model\entity\maalcie\CorveeVoorkeur;
use CsrDelft\model\maalcie\CorveeVoorkeurenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\maalcie\forms\EetwensForm;

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
		return view('maaltijden.voorkeuren.mijn_voorkeuren', [
			'voorkeuren' => $voorkeuren,
			'eetwens' => new EetwensForm(),
		]);
	}

	public function inschakelen($crid) {
		$voorkeur = new CorveeVoorkeur();
		$voorkeur->crv_repetitie_id = $crid;
		$voorkeur->uid = LoginModel::getUid();
		$voorkeur = $this->model->inschakelenVoorkeur($voorkeur);
		return view('maaltijden.voorkeuren.mijn_voorkeur_veld', [
			'uid' => $voorkeur->uid,
			'crid' => $voorkeur->crv_repetitie_id,
		]);
	}

	public function uitschakelen($crid) {
		$voorkeur = new CorveeVoorkeur();
		$voorkeur->crv_repetitie_id = $crid;
		$voorkeur->uid = LoginModel::getUid();
		$voorkeur = $this->model->uitschakelenVoorkeur($voorkeur);
		return view('maaltijden.voorkeuren.mijn_voorkeur_veld', [
			'uid' => $voorkeur->uid,
			'crid' => $voorkeur->crv_repetitie_id,
		]);
	}

	public function eetwens() {
		$form = new EetwensForm();
		if ($form->validate()) {
			$this->model->setEetwens(LoginModel::getProfiel(), $form->getField()->getValue());
		}
		return $form;
	}

}
