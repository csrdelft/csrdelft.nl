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
	/**
	 * @var CorveeVoorkeurenModel
	 */
	private $corveeVoorkeurenModel;

	public function __construct(CorveeVoorkeurenModel $corveeVoorkeurenModel) {
		$this->corveeVoorkeurenModel = $corveeVoorkeurenModel;
	}

	public function mijn() {
		$voorkeuren = $this->corveeVoorkeurenModel->getVoorkeurenVoorLid(LoginModel::getUid(), true);
		return view('maaltijden.voorkeuren.mijn_voorkeuren', [
			'voorkeuren' => $voorkeuren,
			'eetwens' => new EetwensForm(),
		]);
	}

	public function inschakelen($crid) {
		$voorkeur = new CorveeVoorkeur();
		$voorkeur->crv_repetitie_id = $crid;
		$voorkeur->uid = LoginModel::getUid();
		$voorkeur = $this->corveeVoorkeurenModel->inschakelenVoorkeur($voorkeur);
		return view('maaltijden.voorkeuren.mijn_voorkeur_veld', [
			'uid' => $voorkeur->uid,
			'crid' => $voorkeur->crv_repetitie_id,
		]);
	}

	public function uitschakelen($crid) {
		$voorkeur = new CorveeVoorkeur();
		$voorkeur->crv_repetitie_id = $crid;
		$voorkeur->uid = LoginModel::getUid();
		$voorkeur = $this->corveeVoorkeurenModel->uitschakelenVoorkeur($voorkeur);
		return view('maaltijden.voorkeuren.mijn_voorkeur_veld', [
			'uid' => $voorkeur->uid,
			'crid' => $voorkeur->crv_repetitie_id,
		]);
	}

	public function eetwens() {
		$form = new EetwensForm();
		if ($form->validate()) {
			$this->corveeVoorkeurenModel->setEetwens(LoginModel::getProfiel(), $form->getField()->getValue());
		}
		return $form;
	}

}
