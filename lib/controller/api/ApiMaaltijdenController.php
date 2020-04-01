<?php

namespace CsrDelft\controller\api;

use CsrDelft\common\ContainerFacade;
use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use Exception;
use Jacwright\RestServer\RestException;

class ApiMaaltijdenController {
	private $maaltijdenModel;
	private $maaltijdAanmeldingenRepository;

	public function __construct() {
		$container = ContainerFacade::getContainer();

		$this->maaltijdAanmeldingenRepository = $container->get(MaaltijdAanmeldingenRepository::class);
		$this->maaltijdenModel = $container->get(MaaltijdenModel::class);
	}

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
			$maaltijd = $this->maaltijdenModel->getMaaltijd($id);
			$aanmelding = $this->maaltijdAanmeldingenRepository->aanmeldenVoorMaaltijd($maaltijd, $_SESSION['_uid'], $_SESSION['_uid']);
			return array('data' => $aanmelding->maaltijd);
		} catch (Exception $e) {
			throw new RestException(403, $e->getMessage());
		}
	}

	/**
	 * @url POST /$id/afmelden
	 */
	public function maaltijdAfmelden($id) {

		try {
			$maaltijd = $this->maaltijdenModel->getMaaltijd($id);
			$this->maaltijdAanmeldingenRepository->afmeldenDoorLid($maaltijd, $_SESSION['_uid']);
			return array('data' => $maaltijd);
		} catch (Exception $e) {
			throw new RestException(403, $e->getMessage());
		}
	}

}
