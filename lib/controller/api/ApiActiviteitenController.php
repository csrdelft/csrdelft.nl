<?php

namespace CsrDelft\controller\api;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\Security\Voter\Entity\Groep\AbstractGroepVoter;
use CsrDelft\common\Security\Voter\Entity\Groep\ActiviteitGroepVoter;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\repository\ChangeLogRepository;
use CsrDelft\repository\groepen\ActiviteitenRepository;
use CsrDelft\repository\GroepLidRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ApiActiviteitenController extends AbstractController
{
	public function __construct(
		private readonly ActiviteitenRepository $activiteitenRepository,
		private readonly GroepLidRepository $groepLidRepository,
		private readonly ChangeLogRepository $changeLogRepository
	) {
	}

	/**
	 * url POST /$id/aanmelden
	 * @Auth(P_LEDEN_READ)
	 */
	#[Route(path: '/API/2.0/activiteiten/{id}/aanmelden', methods: ['POST'])]
	public function activiteitAanmelden($id)
	{
		$activiteit = $this->activiteitenRepository->get($id);

		if (!$this->isGranted(ActiviteitGroepVoter::BEKIJKEN, $activiteit)) {
			throw $this->createNotFoundException('Activiteit bestaat niet');
		}

		if (!$this->isGranted(ActiviteitGroepVoter::AANMELDEN, $activiteit)) {
			throw $this->createAccessDeniedException('Aanmelden niet mogelijk');
		}

		$lid = $this->groepLidRepository->nieuw($activiteit, $this->getUid());

		$this->changeLogRepository->log($activiteit, 'aanmelden', null, $lid->uid);
		$this->getDoctrine()
			->getManager()
			->persist($lid);
		$this->getDoctrine()
			->getManager()
			->flush();

		return ['data' => $activiteit];
	}

	/**
	 * @Auth(P_LEDEN_READ)
	 */
	#[Route(path: '/API/2.0/activiteiten/{id}/afmelden', methods: ['POST'])]
	public function activiteitAfmelden($id)
	{
		$activiteit = $this->activiteitenRepository->get($id);

		if (!$this->isGranted(ActiviteitGroepVoter::BEKIJKEN, $activiteit)) {
			throw new NotFoundHttpException('Activiteit bestaat niet');
		}

		if (!$this->isGranted(ActiviteitGroepVoter::AFMELDEN, $activiteit)) {
			throw $this->createAccessDeniedException('Afmelden niet mogelijk');
		}

		$lid = $activiteit->getLid($this->getUid());
		$this->changeLogRepository->log($activiteit, 'afmelden', $lid->uid, null);
		$this->getDoctrine()
			->getManager()
			->remove($lid);
		$this->getDoctrine()
			->getManager()
			->flush();

		return ['data' => $activiteit];
	}
}
