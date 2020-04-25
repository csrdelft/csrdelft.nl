<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\corvee\CorveeVoorkeur;
use CsrDelft\repository\corvee\CorveeRepetitiesRepository;
use CsrDelft\repository\corvee\CorveeVoorkeurenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\renderer\TemplateView;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class BeheerVoorkeurenController {
	/**
	 * @var CorveeVoorkeurenRepository
	 */
	private $corveeVoorkeurenRepository;

	public function __construct(CorveeVoorkeurenRepository $corveeVoorkeurenRepository) {
		$this->corveeVoorkeurenRepository = $corveeVoorkeurenRepository;
	}

	public function beheer() {
		list($matrix, $repetities) = $this->corveeVoorkeurenRepository->getVoorkeurenMatrix();
		return view('maaltijden.voorkeur.beheer_voorkeuren', ['matrix' => $matrix, 'repetities' => $repetities]);
	}

	/**
	 * @param ProfielRepository $profielRepository
	 * @param CorveeRepetitiesRepository $corveeRepetitiesRepository
	 * @param $crid
	 * @param $uid
	 * @return TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function inschakelen(ProfielRepository $profielRepository, CorveeRepetitiesRepository $corveeRepetitiesRepository, $crid, $uid) {
		if (!ProfielRepository::existsUid($uid)) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}
		$voorkeur = new CorveeVoorkeur();
		$voorkeur->setProfiel($profielRepository->find($uid));
		$voorkeur->setCorveeRepetitie($corveeRepetitiesRepository->find($crid));

		$voorkeur = $this->corveeVoorkeurenRepository->inschakelenVoorkeur($voorkeur);
		$voorkeur->van_uid = $voorkeur->uid;
		return view('maaltijden.voorkeur.beheer_voorkeur_veld', ['voorkeur' => $voorkeur, 'crid' => $crid, 'uid' => $uid]);
	}

	/**
	 * @param $crid
	 * @param $uid
	 * @return TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function uitschakelen($crid, $uid) {
		if (!ProfielRepository::existsUid($uid)) {
			throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $uid));
		}

		$voorkeur = $this->corveeVoorkeurenRepository->getVoorkeur($crid, $uid);
		$voorkeur->van_uid = $uid;

		$this->corveeVoorkeurenRepository->uitschakelenVoorkeur($voorkeur);

		$voorkeur->setProfiel(null);
		return view('maaltijden.voorkeur.beheer_voorkeur_veld', ['voorkeur' => $voorkeur, 'crid' => $voorkeur->crv_repetitie_id, 'uid' => $voorkeur->uid]);
	}

}
