<?php

namespace CsrDelft\controller\api;

use CsrDelft\model\AbstractGroepLedenModel;
use CsrDelft\model\ChangeLogModel;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\groepen\ActiviteitenModel;
use CsrDelft\model\security\LoginModel;
use \Jacwright\RestServer\RestException;

class ApiActiviteitenController {

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

		$activiteit = ActiviteitenModel::get($id);

		if (!$activiteit || !$activiteit->mag(AccessAction::Bekijken)) {
			throw new RestException(404, 'Activiteit bestaat niet');
		}

		if (!$activiteit->mag(AccessAction::Aanmelden)) {
			throw new RestException(403, 'Aanmelden niet mogelijk');
		}

		$model = $activiteit::getLedenModel();
		$lid = $model->nieuw($activiteit, $_SESSION['_uid']);

		ChangeLogModel::instance()->log($activiteit, 'aanmelden', null, $lid->uid);
		$model->create($lid);

		return array('data' => $activiteit);
	}

	/**
	 * @url POST /$id/afmelden
	 */
	public function activiteitAfmelden($id) {

		$activiteit = ActiviteitenModel::get($id);

		if (!$activiteit || !$activiteit->mag(AccessAction::Bekijken)) {
			throw new RestException(404, 'Activiteit bestaat niet');
		}

		if (!$activiteit->mag(AccessAction::Afmelden)) {
			throw new RestException(403, 'Afmelden niet mogelijk');
		}

		$model = $activiteit::getLedenModel();
		$lid = $model->get($activiteit, $_SESSION['_uid']);
		ChangeLogModel::instance()->log($activiteit, 'afmelden', $lid->uid, null);
		$model->delete($lid);

		return array('data' => $activiteit);
	}

}
