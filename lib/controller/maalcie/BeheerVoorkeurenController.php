<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\maalcie\CorveeVoorkeur;
use CsrDelft\repository\corvee\CorveeVoorkeurenRepository;
use CsrDelft\repository\ProfielRepository;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class BeheerVoorkeurenController {
	/**
	 * @var CorveeVoorkeurenRepository
	 */
	private $corveeVoorkeurenModel;

	public function __construct(CorveeVoorkeurenRepository $corveeVoorkeurenModel) {
		$this->corveeVoorkeurenModel = $corveeVoorkeurenModel;
	}

	public function beheer() {
		list($matrix, $repetities) = $this->corveeVoorkeurenModel->getVoorkeurenMatrix();
		return view('maaltijden.voorkeur.beheer_voorkeuren', ['matrix' => $matrix, 'repetities' => $repetities]);
	}

	public function inschakelen($crid, $uid) {
		if (!ProfielRepository::existsUid($uid)) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}
		$voorkeur = new CorveeVoorkeur();
		$voorkeur->crv_repetitie_id = $crid;
		$voorkeur->uid = $uid;

		$voorkeur = $this->corveeVoorkeurenModel->inschakelenVoorkeur($voorkeur);
		$voorkeur->setVanUid($voorkeur->getUid());
		return view('maaltijden.voorkeur.beheer_voorkeur_veld', ['voorkeur' => $voorkeur, 'crid' => $crid, 'uid' => $uid]);
	}

	public function uitschakelen($crid, $uid) {
		if (!ProfielRepository::existsUid($uid)) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}
		$voorkeur = new CorveeVoorkeur();
		$voorkeur->crv_repetitie_id = (int)$crid;
		$voorkeur->uid = $uid;
		$voorkeur->setVanUid($uid);

		$this->corveeVoorkeurenModel->uitschakelenVoorkeur($voorkeur);

		$voorkeur->uid = null;
		return view('maaltijden.voorkeur.beheer_voorkeur_veld', ['voorkeur' => $voorkeur, 'crid' => $voorkeur->crv_repetitie_id, 'uid' => $voorkeur->uid]);
	}

}
