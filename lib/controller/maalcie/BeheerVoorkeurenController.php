<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\maalcie\CorveeVoorkeur;
use CsrDelft\model\maalcie\CorveeVoorkeurenModel;
use CsrDelft\model\ProfielModel;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class BeheerVoorkeurenController {
	private $model;

	public function __construct() {
		$this->model = CorveeVoorkeurenModel::instance();
	}

	public function beheer() {
		list($matrix, $repetities) = $this->model->getVoorkeurenMatrix();
		return view('maaltijden.voorkeur.beheer_voorkeuren', ['matrix' => $matrix, 'repetities' => $repetities]);
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
		return view('maaltijden.voorkeur.beheer_voorkeur_veld', ['voorkeur' => $voorkeur, 'crid' => $crid, 'uid' => $uid]);
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
		return view('maaltijden.voorkeur.beheer_voorkeur_veld', ['voorkeur' => $voorkeur, 'crid' => $voorkeur->crv_repetitie_id, 'uid' => $voorkeur->uid]);
	}

}
