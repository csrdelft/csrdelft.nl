<?php

namespace CsrDelft\controller\api;

use CsrDelft\common\ContainerFacade;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use Exception;
use Jacwright\RestServer\RestException;

class ApiMaaltijdenController {
	private $maaltijdenRepository;
	private $maaltijdAanmeldingenRepository;

	public function __construct() {
		$container = ContainerFacade::getContainer();

		$this->maaltijdAanmeldingenRepository = $container->get(MaaltijdAanmeldingenRepository::class);
		$this->maaltijdenRepository = $container->get(MaaltijdenRepository::class);
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
			$maaltijd = $this->maaltijdenRepository->getMaaltijd($id);
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
			$maaltijd = $this->maaltijdenRepository->getMaaltijd($id);
			$this->maaltijdAanmeldingenRepository->afmeldenDoorLid($maaltijd, $_SESSION['_uid']);
			return array('data' => $maaltijd);
		} catch (Exception $e) {
			throw new RestException(403, $e->getMessage());
		}
	}

}
