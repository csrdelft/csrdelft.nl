<?php

namespace CsrDelft\controller\api;

use CsrDelft\common\ContainerFacade;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\groepen\ActiviteitenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\ChangeLogRepository;
use Jacwright\RestServer\RestException;

class ApiActiviteitenController {
	/** @var ChangeLogRepository  */
	private $changeLogRepository;
	/** @var ActiviteitenModel  */
	private $activiteitenModel;

	public function __construct() {
		$container = ContainerFacade::getContainer();

		$this->activiteitenModel = $container->get(ActiviteitenModel::class);
		$this->changeLogRepository = $container->get(ChangeLogRepository::class);
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

		$activiteit = $this->activiteitenModel->get($id);

		if (!$activiteit || !$activiteit->mag(AccessAction::Bekijken)) {
			throw new RestException(404, 'Activiteit bestaat niet');
		}

		if (!$activiteit->mag(AccessAction::Aanmelden)) {
			throw new RestException(403, 'Aanmelden niet mogelijk');
		}

		$model = $activiteit::getLedenModel();
		$lid = $model->nieuw($activiteit, $_SESSION['_uid']);

		$this->changeLogRepository->log($activiteit, 'aanmelden', null, $lid->uid);
		$model->create($lid);

		return array('data' => $activiteit);
	}

	/**
	 * @url POST /$id/afmelden
	 */
	public function activiteitAfmelden($id) {

		$activiteit = $this->activiteitenModel->get($id);

		if (!$activiteit || !$activiteit->mag(AccessAction::Bekijken)) {
			throw new RestException(404, 'Activiteit bestaat niet');
		}

		if (!$activiteit->mag(AccessAction::Afmelden)) {
			throw new RestException(403, 'Afmelden niet mogelijk');
		}

		$model = $activiteit::getLedenModel();
		$lid = $model->get($activiteit, $_SESSION['_uid']);
		$this->changeLogRepository->log($activiteit, 'afmelden', $lid->uid, null);
		$model->delete($lid);

		return array('data' => $activiteit);
	}

}
