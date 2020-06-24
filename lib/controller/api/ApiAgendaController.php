<?php

namespace CsrDelft\controller\api;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\agenda\AgendaItem;
use CsrDelft\entity\groepen\Activiteit;
use CsrDelft\entity\groepen\enum\ActiviteitSoort;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\repository\agenda\AgendaRepository;
use CsrDelft\repository\groepen\ActiviteitenRepository;
use CsrDelft\repository\groepen\leden\ActiviteitDeelnemersRepository;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\service\security\LoginService;
use Jacwright\RestServer\RestException;

class ApiAgendaController {
	/** @var ActiviteitenRepository */
	private $activiteitenRepository;
	/** @var AgendaRepository */
	private $agendaRepository;
	/** @var ActiviteitDeelnemersRepository */
	private $activiteitDeelnemersRepository;
	/** @var MaaltijdenRepository */
	private $maaltijdenRepository;
	/** @var MaaltijdAanmeldingenRepository */
	private $maaltijdAanmeldingenRepository;

	public function __construct() {
		$container = ContainerFacade::getContainer();
		$this->agendaRepository = $container->get(AgendaRepository::class);
		$this->activiteitenRepository = $container->get(ActiviteitenRepository::class);
		$this->maaltijdAanmeldingenRepository = $container->get(MaaltijdAanmeldingenRepository::class);
		$this->maaltijdenRepository = $container->get(MaaltijdenRepository::class);
		$this->activiteitDeelnemersRepository = $container->get(ActiviteitDeelnemersRepository::class);
	}

	/**
	 * @return boolean
	 */
	public function authorize() {
		return ApiAuthController::isAuthorized() && LoginService::mag('P_AGENDA_READ');
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
		/** @var AgendaItem[] $items */
		$items = $this->agendaRepository->createQueryBuilder('a')
			->where('a.begin_moment >= :van and a.begin_moment <= :tot')
			->setParameter('van', date_create($_GET['from']))
			->setParameter('tot', date_create($_GET['to']))
			->getQuery()->getResult();
		foreach ($items as $item) {
			if ($item->magBekijken()) {
				$result[] = $item;
			}
		}

		// Activiteiten
		/** @var Activiteit[] $activiteiten */
		$activiteiten = $this->activiteitenRepository->createQueryBuilder('a')
			->where('a.in_agenda = true and (a.begin_moment >= :begin and a.begin_moment <= :eind')
			->setParameter('begin', date_create_immutable("@$from"))
			->setParameter('eind', date_create_immutable("@$to"))
			->getQuery()->getResult();
		$activiteitenFiltered = array();
		foreach ($activiteiten as $activiteit) {
			if (in_array($activiteit->soort, array(ActiviteitSoort::Extern(), ActiviteitSoort::OWee(), ActiviteitSoort::IFES())) OR $activiteit->mag(AccessAction::Bekijken)) {
				$activiteitenFiltered[] = $activiteit;
			}
		}
		$result = array_merge($result, $activiteitenFiltered);

		// Activiteit aanmeldingen
		$activiteitAanmeldingen = array();
		foreach ($activiteitenFiltered as $activiteit) {
			$deelnemer = $this->activiteitDeelnemersRepository->get($activiteit, $_SESSION['_uid']);
			if ($deelnemer) {
				$activiteitAanmeldingen[] = $deelnemer->groep_id;
			}
		}

		// Maaltijden
		$maaltijden = $this->maaltijdenRepository->getMaaltijdenVoorAgenda($from, $to);


		// Maaltijd aanmeldingen
		$mids = array();
		foreach ($maaltijden as $maaltijd) {
			$id = $maaltijd->maaltijd_id;
			$mids[$id] = $maaltijd;

			$maaltijd->gesloten = $maaltijd->gesloten ? '1' : '0';
			$result[] = $maaltijd;

		}
		$maaltijdAanmeldingen = array_keys($this->maaltijdAanmeldingenRepository->getAanmeldingenVoorLid($mids, $_SESSION['_uid']));

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
