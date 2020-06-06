<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\entity\corvee\CorveeRepetitie;
use CsrDelft\entity\corvee\CorveeVoorkeur;
use CsrDelft\repository\corvee\CorveeVoorkeurenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\maalcie\forms\EetwensForm;
use CsrDelft\view\renderer\TemplateView;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MijnVoorkeurenController {
	/**
	 * @var CorveeVoorkeurenRepository
	 */
	private $corveeVoorkeurenRepository;
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;

	public function __construct(CorveeVoorkeurenRepository $corveeVoorkeurenRepository, ProfielRepository $profielRepository) {
		$this->corveeVoorkeurenRepository = $corveeVoorkeurenRepository;
		$this->profielRepository = $profielRepository;
	}

	/**
	 * @return TemplateView
	 * @Route("/corvee/voorkeuren", methods={"GET"})
	 * @Auth(P_CORVEE_IK)
	 */
	public function mijn() {
		$voorkeuren = $this->corveeVoorkeurenRepository->getVoorkeurenVoorLid(LoginService::getUid(), true);
		return view('maaltijden.voorkeuren.mijn_voorkeuren', [
			'voorkeuren' => $voorkeuren,
			'eetwens' => new EetwensForm(),
		]);
	}

	/**
	 * @param CorveeRepetitie $repetitie
	 * @return TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/corvee/voorkeuren/inschakelen/{crv_repetitie_id}", methods={"POST"})
	 * @Auth(P_CORVEE_IK)
	 */
	public function inschakelen(CorveeRepetitie $repetitie) {
		$voorkeur = new CorveeVoorkeur();
		$voorkeur->setProfiel(LoginService::getProfiel());
		$voorkeur->setCorveeRepetitie($repetitie);

		$this->corveeVoorkeurenRepository->inschakelenVoorkeur($voorkeur);

		return view('maaltijden.voorkeuren.mijn_voorkeur_veld', [
			'uid' => $voorkeur->uid,
			'crv_repetitie_id' => $voorkeur->crv_repetitie_id,
		]);
	}

	/**
	 * @param $crv_repetitie_id
	 * @return TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/corvee/voorkeuren/uitschakelen/{crv_repetitie_id}", methods={"POST"})
	 * @Auth(P_CORVEE_IK)
	 */
	public function uitschakelen($crv_repetitie_id) {
		$voorkeur = $this->corveeVoorkeurenRepository->getVoorkeur($crv_repetitie_id, LoginService::getUid());
		$this->corveeVoorkeurenRepository->uitschakelenVoorkeur($voorkeur);

		return view('maaltijden.voorkeuren.mijn_voorkeur_veld', [
			'uid' => $voorkeur->uid,
			'crv_repetitie_id' => $voorkeur->crv_repetitie_id,
		]);
	}

	/**
	 * @return EetwensForm
	 * @Route("/corvee/voorkeuren/eetwens", methods={"POST"})
	 * @Auth(P_CORVEE_IK)
	 */
	public function eetwens() {
		$form = new EetwensForm();
		if ($form->validate()) {
			$this->profielRepository->setEetwens(LoginService::getProfiel(), $form->getField()->getValue());
		}
		return $form;
	}

}
