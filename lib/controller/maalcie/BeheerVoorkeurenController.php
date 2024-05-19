<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\corvee\CorveeRepetitie;
use CsrDelft\entity\corvee\CorveeVoorkeur;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\corvee\CorveeVoorkeurenRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class BeheerVoorkeurenController extends AbstractController
{
	/**
	 * @var CorveeVoorkeurenRepository
	 */
	private $corveeVoorkeurenRepository;

	public function __construct(
		CorveeVoorkeurenRepository $corveeVoorkeurenRepository
	) {
		$this->corveeVoorkeurenRepository = $corveeVoorkeurenRepository;
	}

	/**
  * @return Response
  * @Auth(P_CORVEE_MOD)
  */
 #[Route(path: '/corvee/voorkeuren/beheer', methods: ['GET'])]
 public function beheer(): Response
	{
		list(
			$matrix,
			$repetities,
		) = $this->corveeVoorkeurenRepository->getVoorkeurenMatrix();
		return $this->render('maaltijden/voorkeur/beheer_voorkeuren.html.twig', [
			'matrix' => $matrix,
			'repetities' => $repetities,
		]);
	}

	/**
  * @param CorveeRepetitie $repetitie
  * @param Profiel $profiel
  * @return Response
  * @throws ORMException
  * @throws OptimisticLockException
  * @Auth(P_CORVEE_MOD)
  */
 #[Route(path: '/corvee/voorkeuren/beheer/inschakelen/{crv_repetitie_id}/{uid}', methods: ['POST'])]
 public function inschakelen(CorveeRepetitie $repetitie, Profiel $profiel): Response
	{
		$voorkeur = new CorveeVoorkeur();
		$voorkeur->setProfiel($profiel);
		$voorkeur->setCorveeRepetitie($repetitie);

		$voorkeur = $this->corveeVoorkeurenRepository->inschakelenVoorkeur(
			$voorkeur
		);
		$voorkeur->van_uid = $voorkeur->uid;
		return $this->render('maaltijden/voorkeur/beheer_voorkeur_veld.html.twig', [
			'voorkeur' => $voorkeur,
			'crv_repetitie_id' => $repetitie->crv_repetitie_id,
			'uid' => $profiel->uid,
		]);
	}

	/**
  * @param CorveeVoorkeur $voorkeur
  * @return Response
  * @throws ORMException
  * @throws OptimisticLockException
  * @Auth(P_CORVEE_MOD)
  */
 #[Route(path: '/corvee/voorkeuren/beheer/uitschakelen/{crv_repetitie_id}/{uid}', methods: ['POST'])]
 public function uitschakelen(CorveeVoorkeur $voorkeur): Response
	{
		$voorkeur->van_uid = $voorkeur->uid;

		$this->corveeVoorkeurenRepository->uitschakelenVoorkeur($voorkeur);

		$voorkeur->setProfiel(null);
		return $this->render('maaltijden/voorkeur/beheer_voorkeur_veld.html.twig', [
			'voorkeur' => $voorkeur,
			'crv_repetitie_id' => $voorkeur->crv_repetitie_id,
			'uid' => null,
		]);
	}
}
