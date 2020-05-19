<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\entity\corvee\CorveeVoorkeur;
use CsrDelft\repository\corvee\CorveeRepetitiesRepository;
use CsrDelft\repository\corvee\CorveeVoorkeurenRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\maalcie\forms\EetwensForm;
use CsrDelft\view\renderer\TemplateView;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MijnVoorkeurenController {
	/**
	 * @var CorveeVoorkeurenRepository
	 */
	private $corveeVoorkeurenRepository;

	public function __construct(CorveeVoorkeurenRepository $corveeVoorkeurenRepository) {
		$this->corveeVoorkeurenRepository = $corveeVoorkeurenRepository;
	}

	public function mijn() {
		$voorkeuren = $this->corveeVoorkeurenRepository->getVoorkeurenVoorLid(LoginService::getUid(), true);
		return view('maaltijden.voorkeuren.mijn_voorkeuren', [
			'voorkeuren' => $voorkeuren,
			'eetwens' => new EetwensForm(),
		]);
	}

	/**
	 * @param CorveeRepetitiesRepository $corveeRepetitiesRepository
	 * @param $crid
	 * @return TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function inschakelen(CorveeRepetitiesRepository $corveeRepetitiesRepository, $crid) {
		$voorkeur = new CorveeVoorkeur();
		$voorkeur->setProfiel(LoginService::getProfiel());
		$voorkeur->setCorveeRepetitie($corveeRepetitiesRepository->find($crid));

		$this->corveeVoorkeurenRepository->inschakelenVoorkeur($voorkeur);

		return view('maaltijden.voorkeuren.mijn_voorkeur_veld', [
			'uid' => $voorkeur->uid,
			'crid' => $voorkeur->crv_repetitie_id,
		]);
	}

	/**
	 * @param $crid
	 * @return TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function uitschakelen($crid) {
		$voorkeur = $this->corveeVoorkeurenRepository->getVoorkeur($crid, LoginService::getUid());
		$this->corveeVoorkeurenRepository->uitschakelenVoorkeur($voorkeur);

		return view('maaltijden.voorkeuren.mijn_voorkeur_veld', [
			'uid' => $voorkeur->uid,
			'crid' => $voorkeur->crv_repetitie_id,
		]);
	}

	public function eetwens() {
		$form = new EetwensForm();
		if ($form->validate()) {
			$this->corveeVoorkeurenRepository->setEetwens(LoginService::getProfiel(), $form->getField()->getValue());
		}
		return $form;
	}

}
