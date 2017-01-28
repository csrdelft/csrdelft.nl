<?php

use \Jacwright\RestServer\RestException;

class ApiMaaltijdenController {

	/**
	 * @return boolean
	 */
	public function authorize() {
		return ApiAuthController::isAuthorized() && LoginModel::mag('P_MAAL_IK');
	}

	/**
	 * @url POST /$id/aanmelden
	 */
	public function maaltijdAanmelden($id) {
		require_once 'model/maalcie/MaaltijdAanmeldingenModel.class.php';

		try {
			$aanmelding = MaaltijdAanmeldingenModel::instance()->aanmeldenVoorMaaltijd($id, $_SESSION['_uid'], $_SESSION['_uid']);
			return array('data' => $aanmelding->maaltijd);
		} catch (Exception $e) {
			throw new RestException(403, $e->getMessage());
		}
	}

	/**
	 * @url POST /$id/afmelden
	 */
	public function maaltijdAfmelden($id) {
		require_once 'model/maalcie/MaaltijdAanmeldingenModel.class.php';

		try {
			$maaltijd = MaaltijdAanmeldingenModel::instance()->afmeldenDoorLid($id, $_SESSION['_uid']);
			return array('data' => $maaltijd);
		} catch (Exception $e) {
			throw new RestException(403, $e->getMessage());
		}
	}

}
