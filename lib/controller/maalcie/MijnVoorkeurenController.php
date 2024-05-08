<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\corvee\CorveeRepetitie;
use CsrDelft\entity\corvee\CorveeVoorkeur;
use CsrDelft\repository\corvee\CorveeVoorkeurenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\maalcie\forms\EetwensForm;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MijnVoorkeurenController extends AbstractController
{
	/**
	 * @var CorveeVoorkeurenRepository
	 */
	private $corveeVoorkeurenRepository;
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;

	public function __construct(
		CorveeVoorkeurenRepository $corveeVoorkeurenRepository,
		ProfielRepository $profielRepository
	) {
		$this->corveeVoorkeurenRepository = $corveeVoorkeurenRepository;
		$this->profielRepository = $profielRepository;
	}

	/**
  * @return Response
  * @Auth(P_CORVEE_IK)
  */
 #[Route(path: '/corvee/voorkeuren', methods: ['GET'])]
 public function mijn(): Response
	{
		$voorkeuren = $this->corveeVoorkeurenRepository->getVoorkeurenVoorLid(
			$this->getUid(),
			true
		);
		return $this->render('maaltijden/voorkeuren/mijn_voorkeuren.html.twig', [
			'voorkeuren' => $voorkeuren,
			'eetwens' => new EetwensForm(),
		]);
	}

	/**
  * @param CorveeRepetitie $repetitie
  * @return Response
  * @throws ORMException
  * @throws OptimisticLockException
  * @Auth(P_CORVEE_IK)
  */
 #[Route(path: '/corvee/voorkeuren/inschakelen/{crv_repetitie_id}', methods: ['POST'])]
 public function inschakelen(CorveeRepetitie $repetitie): Response
	{
		$voorkeur = new CorveeVoorkeur();
		$voorkeur->setProfiel($this->getProfiel());
		$voorkeur->setCorveeRepetitie($repetitie);

		$this->corveeVoorkeurenRepository->inschakelenVoorkeur($voorkeur);

		return $this->render('maaltijden/voorkeuren/mijn_voorkeur_veld.html.twig', [
			'uid' => $voorkeur->uid,
			'crv_repetitie_id' => $voorkeur->crv_repetitie_id,
		]);
	}

	/**
  * @param $crv_repetitie_id
  * @return Response
  * @throws ORMException
  * @throws OptimisticLockException
  * @Auth(P_CORVEE_IK)
  */
 #[Route(path: '/corvee/voorkeuren/uitschakelen/{crv_repetitie_id}', methods: ['POST'])]
 public function uitschakelen($crv_repetitie_id): Response
	{
		$voorkeur = $this->corveeVoorkeurenRepository->getVoorkeur(
			$crv_repetitie_id,
			$this->getUid()
		);
		$this->corveeVoorkeurenRepository->uitschakelenVoorkeur($voorkeur);

		return $this->render('maaltijden/voorkeuren/mijn_voorkeur_veld.html.twig', [
			'uid' => $voorkeur->uid,
			'crv_repetitie_id' => $voorkeur->crv_repetitie_id,
		]);
	}

	/**
  * @return EetwensForm
  * @Auth(P_CORVEE_IK)
  */
 #[Route(path: '/corvee/voorkeuren/eetwens', methods: ['POST'])]
 public function eetwens(): EetwensForm
	{
		$form = new EetwensForm();
		if ($form->validate()) {
			$this->profielRepository->setEetwens(
				$this->getProfiel(),
				$form->getField()->getValue()
			);
		}
		return $form;
	}
}
