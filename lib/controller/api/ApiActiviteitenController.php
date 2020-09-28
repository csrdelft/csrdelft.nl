<?php

namespace CsrDelft\controller\api;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\repository\ChangeLogRepository;
use CsrDelft\repository\groepen\ActiviteitenRepository;
use CsrDelft\repository\groepen\leden\ActiviteitDeelnemersRepository;
use CsrDelft\service\security\LoginService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ApiActiviteitenController extends AbstractController {
	/** @var ChangeLogRepository  */
	private $changeLogRepository;
	/** @var ActiviteitenRepository  */
	private $activiteitenRepository;
	/**
	 * @var ActiviteitDeelnemersRepository
	 */
	private $activiteitDeelnemersRepository;

	public function __construct(ActiviteitenRepository  $activiteitenRepository, ActiviteitDeelnemersRepository $activiteitDeelnemersRepository, ChangeLogRepository  $changeLogRepository) {
		$this->activiteitenRepository = $activiteitenRepository;
		$this->activiteitDeelnemersRepository = $activiteitDeelnemersRepository;
		$this->changeLogRepository = $changeLogRepository;
	}

	/**
	 * url POST /$id/aanmelden
	 * @Route("/API/2.0/activiteiten/{id}/aanmelden", methods={"POST"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function activiteitAanmelden($id) {
		$activiteit = $this->activiteitenRepository->get($id);

		if (!$activiteit || !$activiteit->mag(AccessAction::Bekijken)) {
			throw new NotFoundHttpException('Activiteit bestaat niet');
		}

		if (!$activiteit->mag(AccessAction::Aanmelden)) {
			throw $this->createAccessDeniedException('Aanmelden niet mogelijk');
		}

		$lid = $this->activiteitDeelnemersRepository->nieuw($activiteit, $_SESSION[LoginService::SESS_UID]);

		$this->changeLogRepository->log($activiteit, 'aanmelden', null, $lid->uid);
		$this->getDoctrine()->getManager()->persist($lid);
		$this->getDoctrine()->getManager()->flush();

		return array('data' => $activiteit);
	}

	/**
	 * @Route("/API/2.0/activiteiten/{id}/afmelden", methods={"POST"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function activiteitAfmelden($id) {
		$activiteit = $this->activiteitenRepository->get($id);

		if (!$activiteit || !$activiteit->mag(AccessAction::Bekijken)) {
			throw new NotFoundHttpException('Activiteit bestaat niet');
		}

		if (!$activiteit->mag(AccessAction::Afmelden)) {
			throw $this->createAccessDeniedException('Afmelden niet mogelijk');
		}

		$lid = $activiteit->getLid($_SESSION['_uid']);
		$this->changeLogRepository->log($activiteit, 'afmelden', $lid->uid, null);
		$this->getDoctrine()->getManager()->remove($lid);
		$this->getDoctrine()->getManager()->flush();

		return array('data' => $activiteit);
	}

}
