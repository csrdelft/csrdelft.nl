<?php

namespace CsrDelft\controller\api;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\agenda\AgendaItem;
use CsrDelft\entity\groepen\Activiteit;
use CsrDelft\entity\groepen\enum\ActiviteitSoort;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\repository\agenda\AgendaRepository;
use CsrDelft\repository\groepen\ActiviteitenRepository;
use CsrDelft\repository\GroepLidRepository;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ApiAgendaController extends AbstractController
{
	/** @var ActiviteitenRepository */
	private $activiteitenRepository;
	/** @var AgendaRepository */
	private $agendaRepository;
	/** @var GroepLidRepository */
	private $groepLidRepository;
	/** @var MaaltijdenRepository */
	private $maaltijdenRepository;
	/** @var MaaltijdAanmeldingenRepository */
	private $maaltijdAanmeldingenRepository;

	public function __construct(
		AgendaRepository $agendaRepository,
		ActiviteitenRepository $activiteitenRepository,
		MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository,
		MaaltijdenRepository $maaltijdenRepository,
		GroepLidRepository $groepLidRepository
	) {
		$this->agendaRepository = $agendaRepository;
		$this->activiteitenRepository = $activiteitenRepository;
		$this->maaltijdAanmeldingenRepository = $maaltijdAanmeldingenRepository;
		$this->maaltijdenRepository = $maaltijdenRepository;
		$this->groepLidRepository = $groepLidRepository;
	}

	/**
	 * @Route("/API/2.0/agenda", methods={"GET"})
	 * @Auth(P_AGENDA_READ)
	 * @return JsonResponse
	 */
	public function getAgenda()
	{
		if (!isset($_GET['from']) || !isset($_GET['to'])) {
			throw new BadRequestHttpException();
		}

		$from = strtotime($_GET['from']);
		$to = strtotime($_GET['to']);


		$result = array();

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
			->where('a.inAgenda = true and (a.beginMoment >= :begin and a.beginMoment <= :eind)')
			->setParameter('begin', date_create_immutable("@$from"))
			->setParameter('eind', date_create_immutable("@$to"))
			->getQuery()->getResult();
		$activiteitenFiltered = array();
		foreach ($activiteiten as $activiteit) {
			if (in_array($activiteit->activiteitSoort, array(ActiviteitSoort::Extern(), ActiviteitSoort::OWee(), ActiviteitSoort::IFES())) or $activiteit->mag(AccessAction::Bekijken())) {
				$activiteitenFiltered[] = $activiteit;
			}
		}
		$result = array_merge($result, $activiteitenFiltered);

		// Activiteit aanmeldingen
		$activiteitAanmeldingen = array();
		foreach ($activiteitenFiltered as $activiteit) {
			$deelnemer = $this->groepLidRepository->get($activiteit, $this->getUid());
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
		$maaltijdAanmeldingen = array_keys($this->maaltijdAanmeldingenRepository->getAanmeldingenVoorLid($mids, $this->getUid()));

		// Sorteren
		usort($result, array(AgendaRepository::class, 'vergelijkAgendeerbaars'));

		$agenda = array(
			'events' => $result,
			'joined' => array(
				'maaltijden' => $maaltijdAanmeldingen,
				'activiteiten' => $activiteitAanmeldingen
			)
		);

		return new JsonResponse(array('data' => $agenda));
	}

}
