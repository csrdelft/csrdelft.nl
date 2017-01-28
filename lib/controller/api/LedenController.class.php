<?php

use \Jacwright\RestServer\RestException;

class ApiLedenController {

	/**
	 * @return boolean
	 */
	public function authorize() {
		return ApiAuthController::isAuthorized() && LoginModel::mag('P_OUDLEDEN_READ');
	}

	/**
	 * @url GET /
	 */
	public function getLeden() {
		require_once 'lid/lidzoeker.class.php';

		$zoeker = new LidZoeker();
		$leden = [];

		foreach($zoeker->getLeden() as $profiel) {
			$leden[] = array(
				'id'            => $profiel->uid,
				'voornaam'      => $profiel->voornaam,
				'tussenvoegsel' => $profiel->tussenvoegsel,
				'achternaam'    => $profiel->achternaam
			);
		}

		return array('data' => $leden);
	}

	/**
	 * @url GET /$id
	 */
	public function getLid($id) {
		$profiel = ProfielModel::get($id);

		if (!$profiel) {
			throw new RestException(404);
		}

		$lid = array(
			'id'               => $profiel->uid,
			'naam' => array(
				'voornaam'       => $profiel->voornaam,
				'tussenvoegsel'  => $profiel->tussenvoegsel,
				'achternaam'     => $profiel->achternaam,
				'formeel'        => $profiel->getNaam('civitas')
			),
			'pasfoto'          => $profiel->getPasfotoPath(true),
			'geboortedatum'    => $profiel->gebdatum,
			'email'            => $profiel->email,
			'mobiel'           => $profiel->mobiel,
			'huis' => array(
				'naam'           => $profiel->getWoonoord()->naam,
				'adres'          => $profiel->adres,
				'postcode'       => $profiel->postcode,
				'woonplaats'     => $profiel->woonplaats,
				'land'           => $profiel->land
			),
			'studie' => array(
				'naam'           => $profiel->studie,
				'sinds'          => $profiel->studiejaar
			),
			'lichting'         => $profiel->lidjaar,
			'verticale'        => $profiel->getVerticale()->naam,
		);

		return array('data' => $lid);
	}

}
