<?php

namespace CsrDelft\controller\api;

use CsrDelft\common\ContainerFacade;
use CsrDelft\model\entity\groepen\Activiteit;
use CsrDelft\model\entity\groepen\ActiviteitSoort;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\groepen\ActiviteitenModel;
use CsrDelft\model\groepen\leden\ActiviteitDeelnemersModel;
use CsrDelft\model\maalcie\MaaltijdAanmeldingenModel;
use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\agenda\AgendaRepository;
use Jacwright\RestServer\RestException;

class ApiAgendaController {

	/**
	 * @return boolean
	 */
	public function authorize() {
		return ApiAuthController::isAuthorized() && LoginModel::mag('P_AGENDA_READ');
	}

	/**
	 * @url GET /
	 * @param string from
	 * @param string to
	 * @return array
	 * @throws RestException
	 */
	public function getAgenda() {
		if (!isset($_GET['from']) || !isset($_GET['to'])) {
			throw new RestException(400);
		}

		$from = strtotime($_GET['from']);
		$to = strtotime($_GET['to']);


		$result = array();

		$fromDate = date('Y-m-d', $from);
		$toDate = date('Y-m-d', $to);
		$query = '(begin_moment >= ? AND begin_moment <= ?)';
		$find = array($fromDate, $toDate);

		// AgendaItems
		$agendaRepository = ContainerFacade::getContainer()->get(AgendaRepository::class);
		$items = $agendaRepository->ormFind($query, $find);
		foreach ($items as $item) {
			if ($item->magBekijken()) {
				$result[] = $item;
			}
		}

		// Activiteiten
		/** @var Activiteit[] $activiteiten */
		$activiteiten = ActiviteitenModel::instance()->find('in_agenda = TRUE AND (' . $query . ')', $find);
		$activiteitenFiltered = array();
		foreach ($activiteiten as $activiteit) {
			if (in_array($activiteit->soort, array(ActiviteitSoort::Extern, ActiviteitSoort::OWee, ActiviteitSoort::IFES)) OR $activiteit->mag(AccessAction::Bekijken)) {
				$activiteitenFiltered[] = $activiteit;
			}
		}
		$result = array_merge($result, $activiteitenFiltered);

		// Activiteit aanmeldingen
		$activiteitAanmeldingen = array();
		foreach ($activiteitenFiltered as $activiteit) {
			$deelnemer = ActiviteitDeelnemersModel::instance()->get($activiteit, $_SESSION['_uid']);
			if ($deelnemer) {
				$activiteitAanmeldingen[] = $deelnemer->groep_id;
			}
		}

		// Maaltijden
		$maaltijden = MaaltijdenModel::instance()->getMaaltijdenVoorAgenda($from, $to);


		// Maaltijd aanmeldingen
		$mids = array();
		foreach ($maaltijden as $maaltijd) {
			$id = $maaltijd->maaltijd_id;
			$mids[$id] = $maaltijd;

			$maaltijd->gesloten = $maaltijd->gesloten ? '1' : '0';
			$result[] = $maaltijd;

		}
		$maaltijdAanmeldingen = array_keys(MaaltijdAanmeldingenModel::instance()->getAanmeldingenVoorLid($mids, $_SESSION['_uid']));

		// Sorteren
		usort($result, array(AgendaRepository::class, 'vergelijkAgendeerbaars'));

		$agenda = array(
			'events' => $result,
			'joined' => array(
				'maaltijden' => $maaltijdAanmeldingen,
				'activiteiten' => $activiteitAanmeldingen
			)
		);

		return array('data' => $agenda);
	}

}
