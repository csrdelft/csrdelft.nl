<?php

namespace CsrDelft\controller\api;

use CsrDelft\model\maalcie\MaaltijdAanmeldingenModel;
use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\model\security\LoginModel;
use \Jacwright\RestServer\RestException;

class ApiMaaltijdenController {

	/**
	 * @return boolean
	 */
	public function authorize() {
		return ApiAuthController::isAuthorized() && LoginModel::mag(P_MAAL_IK);
	}

	/**
	 * @url POST /$id/aanmelden
	 */
	public function maaltijdAanmelden($id) {

		try {
			$maaltijd = MaaltijdenModel::instance()->getMaaltijd($id);
			$aanmelding = MaaltijdAanmeldingenModel::instance()->aanmeldenVoorMaaltijd($maaltijd, $_SESSION['_uid'], $_SESSION['_uid']);
			return array('data' => $aanmelding->maaltijd);
		} catch (\Exception $e) {
			throw new RestException(403, $e->getMessage());
		}
	}

	/**
	 * @url POST /$id/afmelden
	 */
	public function maaltijdAfmelden($id) {

		try {
			$maaltijd = MaaltijdenModel::instance()->getMaaltijd($id);
			MaaltijdAanmeldingenModel::instance()->afmeldenDoorLid($maaltijd, $_SESSION['_uid']);
			return array('data' => $maaltijd);
		} catch (\Exception $e) {
			throw new RestException(403, $e->getMessage());
		}
	}

}
