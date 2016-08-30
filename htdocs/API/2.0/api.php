<?php

use Firebase\JWT\JWT;

require_once 'configuratie.include.php';

// Handle preflight requests for local development CORS
if (DEBUG === true && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) && ($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] === 'GET' || $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] === 'POST')) {
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Headers: X-CSR-Authorization');
	}
	exit;
}

// Get the authorization http header
$headers = apache_request_headers();
$authHeader = $headers['X-Csr-Authorization'];

if ($authHeader) {

	// Check the header for a JWT
	$jwt = substr($authHeader, 7);

	if ($jwt) {
		try {

			// Try to decode the JWT
			$token = JWT::decode($jwt, JWT_SECRET, array('HS512'));

			// Register uid for this session
			$_SESSION['_uid'] = $token->data->userId;

			// Leden
			if (LoginModel::mag('P_OUDLEDEN_READ') && isset($_GET['cat']) && $_GET['cat'] === 'leden') {
				if (isset($_GET['id'])) {
					$get = getLid($_GET['id']);
				} else {
					$get = getLeden();
				}
			}

			// Agenda
			elseif (LoginModel::mag('P_AGENDA_READ') && isset($_GET['cat']) && $_GET['cat'] === 'agenda') {
				if (isset($_GET['from']) && isset($_GET['to'])) {
					$from = strtotime($_GET['from']);
					$to = strtotime($_GET['to']);
					if ($from && $to) {
						$get = getAgenda($from, $to);
					}
				}
			}

			// Maaltijden
			elseif (LoginModel::mag('P_MAAL_IK') && isset($_GET['cat']) && $_GET['cat'] === 'maaltijden') {
				if (isset($_GET['id']) && isset($_GET['action'])) {
					if ($_GET['action'] === 'aanmelden') {
						$get = maaltijdAanmelden(intval($_GET['id']));
					} elseif ($_GET['action'] === 'afmelden') {
						$get = maaltijdAfmelden(intval($_GET['id']));
					}
				}
			}

			// Activiteiten
			elseif (LoginModel::mag('P_LEDEN_READ') && isset($_GET['cat']) && $_GET['cat'] === 'activiteiten') {
				if (isset($_GET['id']) && isset($_GET['action'])) {
					if ($_GET['action'] === 'aanmelden') {
						$get = activiteitAanmelden(intval($_GET['id']));
					} elseif ($_GET['action'] === 'afmelden') {
						$get = activiteitAfmelden(intval($_GET['id']));
					}
				}
			}

			if (!isset($get)) {

				// Invalid request
				http_response_code(400);
				exit;

			}

			// Respond
			$data = array('data' => $get);

			header('Content-type: application/json');
			echo json_encode($data);
			exit;

		} catch (Exception $e) {

			// Bad token
			http_response_code(401);
			exit;

		}

	} else {

		// No token found
		http_response_code(400);
		exit;

	}

} else {

	// No authorization header
	http_response_code(400);
	exit;

}


function getLid($id) {
	$profiel = ProfielModel::get($id);

	if ($profiel) {
		return array(
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
	} else {
		header('HTTP/1.0 404 Not Found');
		exit;
	}
}


function getLeden() {

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

	return $leden;
}


function getAgenda($from, $to) {

	require_once 'model/maalcie/MaaltijdenModel.class.php';
	require_once 'model/maalcie/MaaltijdAanmeldingenModel.class.php';
	require_once 'model/AgendaModel.class.php';
	require_once 'model/GroepenModel.abstract.php';
	require_once 'model/GroepLedenModel.abstract.php';

	$result = array();

	$fromDate = date('Y-m-d', $from);
	$toDate = date('Y-m-d', $to);
	$query = '(begin_moment >= ? AND begin_moment <= ?)';
	$find = array($fromDate, $toDate);

	// AgendaItems
	$items = AgendaModel::instance()->find($query, $find);
	foreach ($items as $item) {
		if ($item->magBekijken()) {
			$result[] = $item;
		}
	}

	// Activiteiten
	$activiteiten = ActiviteitenModel::instance()->find('in_agenda = TRUE AND (' . $query . ')', $find);
	$activiteitenFiltered = array();
	foreach ($activiteiten as $activiteit) {
		if (in_array($activiteit->soort, array(ActiviteitSoort::Extern, ActiviteitSoort::OWee, ActiviteitSoort::IFES)) OR $activiteit->mag(A::Bekijken)) {
			$activiteitenFiltered[] = $activiteit;
		}
	}
	$result = array_merge($result, $activiteitenFiltered);

	// Activiteit aanmeldingen
	$activiteitAanmeldingen = array();
	foreach ($activiteitenFiltered as $activiteit) {
		$deelnemer = ActiviteitDeelnemersModel::get($activiteit, $_SESSION['_uid']);
		if ($deelnemer) {
			$activiteitAanmeldingen[] = $deelnemer->groep_id;
		}
	}

	// Maaltijden
	$maaltijden = MaaltijdenModel::getMaaltijdenVoorAgenda($from, $to);
	$result = array_merge($result, $maaltijden);

	// Maaltijd aanmeldingen
	$mids = array();
	foreach ($maaltijden as $maaltijd) {
		$id = $maaltijd->getMaaltijdId();
		$mids[$id] = $maaltijd;
	}
	$maaltijdAanmeldingen = array_keys(MaaltijdAanmeldingenModel::getAanmeldingenVoorLid($mids, $_SESSION['_uid']));

	// Sorteren
	usort($result, array('AgendaModel', 'vergelijkAgendeerbaars'));

	return array(
		'events' => $result,
		'joined' => array(
			'maaltijden' => $maaltijdAanmeldingen,
			'activiteiten' => $activiteitAanmeldingen
		)
	);
}


function maaltijdAanmelden($id) {

	require_once 'model/maalcie/MaaltijdAanmeldingenModel.class.php';

	try {
		$aanmelding = MaaltijdAanmeldingenModel::aanmeldenVoorMaaltijd($id, $_SESSION['_uid'], $_SESSION['_uid']);
		return $aanmelding->getMaaltijd();
	} catch (Exception $e) {
		http_response_code(403);
		return $e->getMessage();
	}
}

function maaltijdAfmelden($id) {

	require_once 'model/maalcie/MaaltijdAanmeldingenModel.class.php';

	try {
		$maaltijd = MaaltijdAanmeldingenModel::afmeldenDoorLid($id, $_SESSION['_uid']);
		return $maaltijd;
	} catch (Exception $e) {
		http_response_code(403);
		return $e->getMessage();
	}
}


function activiteitAanmelden($id) {

	require_once 'model/GroepenModel.abstract.php';
	require_once 'model/ChangeLogModel.class.php';

	$activiteit = ActiviteitenModel::get($id);

	if (!$activiteit || !$activiteit->mag(A::Bekijken)) {
		http_response_code(404);
		return 'Activiteit bestaat niet.';
	}

	if (!$activiteit->mag(A::Aanmelden)) {
		http_response_code(403);
		return 'Aanmelden niet mogelijk';
	}

	$leden = $activiteit::leden;
	$model = $leden::instance();
	$lid = $model->nieuw($activiteit, $_SESSION['_uid']);

	ChangeLogModel::instance()->log($activiteit, 'aanmelden', null, $lid->uid);
	$model->create($lid);

	return $activiteit;
}

function activiteitAfmelden($id) {

	require_once 'model/GroepenModel.abstract.php';
	require_once 'model/ChangeLogModel.class.php';

	$activiteit = ActiviteitenModel::get($id);

	if (!$activiteit || !$activiteit->mag(A::Bekijken)) {
		http_response_code(404);
		return 'Activiteit bestaat niet';
	}

	if (!$activiteit->mag(A::Afmelden)) {
		http_response_code(403);
		return 'Afmelden niet mogelijk';
	}

	$leden = $activiteit::leden;
	$model = $leden::instance();
	$lid = $model->get($activiteit, $_SESSION['_uid']);
	ChangeLogModel::instance()->log($activiteit, 'afmelden', $lid->uid, null);
	$model->delete($lid);

	return $activiteit;
}
