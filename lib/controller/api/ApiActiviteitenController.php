<?php

namespace CsrDelft\controller\api;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\Security\Voter\Entity\Groep\AbstractGroepVoter;
use CsrDelft\common\Security\Voter\Entity\Groep\ActiviteitGroepVoter;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\repository\ChangeLogRepository;
use CsrDelft\repository\groepen\ActiviteitenRepository;
use CsrDelft\repository\GroepLidRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ApiActiviteitenController extends AbstractController
{
	/** @var ChangeLogRepository  */
	private $changeLogRepository;
	/** @var ActiviteitenRepository  */
	private $activiteitenRepository;
	/**
	 * @var GroepLidRepository
	 */
	private $groepLidRepository;

	public function __construct(
		ActiviteitenRepository $activiteitenRepository,
		GroepLidRepository $groepLidRepository,
		ChangeLogRepository $changeLogRepository
	) {
		$this->activiteitenRepository = $activiteitenRepository;
		$this->groepLidRepository = $groepLidRepository;
		$this->changeLogRepository = $changeLogRepository;
	}

	/**
	 * url POST /$id/aanmelden
	 * @Route("/API/2.0/activiteiten/{id}/aanmelden", methods={"POST"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function activiteitAanmelden($id): array
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
	 * @Route("/API/2.0/activiteiten/{id}/afmelden", methods={"POST"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function activiteitAfmelden($id): array
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
