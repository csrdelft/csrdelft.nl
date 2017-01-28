<?php

use \Jacwright\RestServer\RestException;

class ApiActiviteitenController {

	/**
	 * @return boolean
	 */
	public function authorize() {
		return ApiAuthController::isAuthorized() && LoginModel::mag('P_LEDEN_READ');
	}

	/**
	 * @url POST /$id/aanmelden
	 */
	public function activiteitAanmelden($id) {
		require_once 'model/GroepenModel.abstract.php';
		require_once 'model/ChangeLogModel.class.php';

		$activiteit = ActiviteitenModel::get($id);

		if (!$activiteit || !$activiteit->mag(A::Bekijken)) {
			throw new RestException(404, 'Activiteit bestaat niet');
		}

		if (!$activiteit->mag(A::Aanmelden)) {
			throw new RestException(403, 'Aanmelden niet mogelijk');
		}

		$leden = $activiteit::leden;
		$model = $leden::instance();
		$lid = $model->nieuw($activiteit, $_SESSION['_uid']);

		ChangeLogModel::instance()->log($activiteit, 'aanmelden', null, $lid->uid);
		$model->create($lid);

		return array('data' => $activiteit);
	}

	/**
	 * @url POST /$id/afmelden
	 */
	public function activiteitAfmelden($id) {
		require_once 'model/GroepenModel.abstract.php';
		require_once 'model/ChangeLogModel.class.php';

		$activiteit = ActiviteitenModel::get($id);

		if (!$activiteit || !$activiteit->mag(A::Bekijken)) {
			throw new RestException(404, 'Activiteit bestaat niet');
		}

		if (!$activiteit->mag(A::Afmelden)) {
			throw new RestException(403, 'Afmelden niet mogelijk');
		}

		$leden = $activiteit::leden;
		$model = $leden::instance();
		$lid = $model->get($activiteit, $_SESSION['_uid']);
		ChangeLogModel::instance()->log($activiteit, 'afmelden', $lid->uid, null);
		$model->delete($lid);

		return array('data' => $activiteit);
	}

}
