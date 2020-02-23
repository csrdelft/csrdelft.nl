<?php

namespace CsrDelft\controller\api;

use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\LidZoeker;
use Jacwright\RestServer\RestException;

class ApiLedenController {

	/**
	 * @return boolean
	 */
	public function authorize() {
		return ApiAuthController::isAuthorized() && LoginModel::mag(P_OUDLEDEN_READ);
	}

	/**
	 * @url GET /
	 */
	public function getLeden() {

		$zoeker = new LidZoeker();
		$leden = [];

		foreach ($zoeker->getLeden() as $profiel) {
			$leden[] = array(
				'id' => $profiel->uid,
				'voornaam' => $profiel->voornaam,
				'tussenvoegsel' => $profiel->tussenvoegsel,
				'achternaam' => $profiel->achternaam
			);
		}

		return array('data' => $leden);
	}

	/**
	 * @url GET /$id
	 */
	public function getLid($id) {
		$profiel = ProfielRepository::get($id);

		if (!$profiel) {
			throw new RestException(404);
		}

		$woonoord = $profiel->getWoonoord();
		$lid = array(
			'id' => $profiel->uid,
			'naam' => array(
				'voornaam' => $profiel->voornaam,
				'tussenvoegsel' => $profiel->tussenvoegsel,
				'achternaam' => $profiel->achternaam,
				'formeel' => $profiel->getNaam('civitas')
			),
			'pasfoto' => $profiel->getPasfotoPath('vierkant'),
			'geboortedatum' => $profiel->gebdatum->format(DATE_FORMAT),
			'email' => $profiel->email,
			'mobiel' => $profiel->mobiel,
			'huis' => array(
				'naam' => $woonoord ? $woonoord->naam : null,
				'adres' => $profiel->adres,
				'postcode' => $profiel->postcode,
				'woonplaats' => $profiel->woonplaats,
				'land' => $profiel->land
			),
			'studie' => array(
				'naam' => $profiel->studie,
				'sinds' => $profiel->studiejaar
			),
			'lichting' => $profiel->lidjaar,
			'verticale' => $profiel->getVerticale() === false ? null : $profiel->getVerticale()->naam,
		);

		return array('data' => $lid);
	}

}
