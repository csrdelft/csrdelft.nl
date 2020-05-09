<?php

namespace CsrDelft\controller\api;

use CsrDelft\common\ContainerFacade;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\repository\groepen\ActiviteitenRepository;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\ChangeLogRepository;
use CsrDelft\repository\groepen\leden\ActiviteitDeelnemersRepository;
use Jacwright\RestServer\RestException;

class ApiActiviteitenController {
	/** @var ChangeLogRepository  */
	private $changeLogRepository;
	/** @var ActiviteitenRepository  */
	private $activiteitenRepository;
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;
	/**
	 * @var ActiviteitDeelnemersRepository
	 */
	private $activiteitDeelnemersRepository;

	public function __construct() {
		$container = ContainerFacade::getContainer();

		$this->activiteitenRepository = $container->get(ActiviteitenRepository::class);
		$this->activiteitDeelnemersRepository = $container->get(ActiviteitDeelnemersRepository::class);
		$this->changeLogRepository = $container->get(ChangeLogRepository::class);
		$this->em = $container->get('doctrine.orm.entity_manager');
	}

	/**
	 * @return boolean
	 */
	public function authorize() {
		return ApiAuthController::isAuthorized() && LoginModel::mag(P_LEDEN_READ);
	}

	/**
	 * @url POST /$id/aanmelden
	 */
	public function activiteitAanmelden($id) {

		$activiteit = $this->activiteitenRepository->get($id);

		if (!$activiteit || !$activiteit->mag(AccessAction::Bekijken)) {
			throw new RestException(404, 'Activiteit bestaat niet');
		}

		if (!$activiteit->mag(AccessAction::Aanmelden)) {
			throw new RestException(403, 'Aanmelden niet mogelijk');
		}

		$lid = $this->activiteitDeelnemersRepository->nieuw($activiteit, $_SESSION['_uid']);

		$this->changeLogRepository->log($activiteit, 'aanmelden', null, $lid->uid);
		$this->em->persist($lid);
		$this->em->flush();

		return array('data' => $activiteit);
	}

	/**
	 * @url POST /$id/afmelden
	 */
	public function activiteitAfmelden($id) {

		$activiteit = $this->activiteitenRepository->get($id);

		if (!$activiteit || !$activiteit->mag(AccessAction::Bekijken)) {
			throw new RestException(404, 'Activiteit bestaat niet');
		}

		if (!$activiteit->mag(AccessAction::Afmelden)) {
			throw new RestException(403, 'Afmelden niet mogelijk');
		}

		$lid = $activiteit->getLid($_SESSION['_uid']);
		$this->changeLogRepository->log($activiteit, 'afmelden', $lid->uid, null);
		$this->em->remove($lid);
		$this->em->flush();

		return array('data' => $activiteit);
	}

}
