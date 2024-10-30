<?php

namespace CsrDelft\controller\api;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\Security\Voter\Entity\Groep\AbstractGroepVoter;
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
use CsrDelft\service\maalcie\MaaltijdenService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class ApiAgendaController extends AbstractController
{
	public function __construct(
		private readonly AgendaRepository $agendaRepository,
		private readonly ActiviteitenRepository $activiteitenRepository,
		private readonly MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository,
		private readonly MaaltijdenService $maaltijdenService,
		private readonly GroepLidRepository $groepLidRepository
	) {
	}

	/**
	 * @Auth(P_AGENDA_READ)
	 * @return JsonResponse
	 */
	#[Route(path: '/API/2.0/agenda', methods: ['GET'])]
	public function getAgenda()
	{
		if (!isset($_GET['from']) || !isset($_GET['to'])) {
			throw new BadRequestHttpException();
		}

		$from = strtotime((string) $_GET['from']);
		$to = strtotime((string) $_GET['to']);

		$result = [];

		// AgendaItems
		/** @var AgendaItem[] $items */
		$items = $this->agendaRepository
			->createQueryBuilder('a')
			->where('a.begin_moment >= :van and a.begin_moment <= :tot')
			->setParameter('van', date_create($_GET['from']))
			->setParameter('tot', date_create($_GET['to']))
			->getQuery()
			->getResult();
		foreach ($items as $item) {
			if ($item->magBekijken()) {
				$result[] = $item;
			}
		}

		// Activiteiten
		/** @var Activiteit[] $activiteiten */
		$activiteiten = $this->activiteitenRepository
			->createQueryBuilder('a')
			->where(
				'a.inAgenda = true and (a.beginMoment >= :begin and a.beginMoment <= :eind)'
			)
			->setParameter('begin', date_create_immutable("@$from"))
			->setParameter('eind', date_create_immutable("@$to"))
			->getQuery()
			->getResult();
		$activiteitenFiltered = [];
		foreach ($activiteiten as $activiteit) {
			if (
				in_array($activiteit->activiteitSoort, [
					ActiviteitSoort::Extern(),
					ActiviteitSoort::OWee(),
					ActiviteitSoort::IFES(),
				]) or $this->isGranted(AbstractGroepVoter::BEKIJKEN, $activiteit)
			) {
				$activiteitenFiltered[] = $activiteit;
			}
		}
		$result = array_merge($result, $activiteitenFiltered);

		// Activiteit aanmeldingen
		$activiteitAanmeldingen = [];
		foreach ($activiteitenFiltered as $activiteit) {
			$deelnemer = $this->groepLidRepository->get($activiteit, $this->getUid());
			if ($deelnemer) {
				$activiteitAanmeldingen[] = $deelnemer->groep_id;
			}
		}

		// Maaltijden
		$maaltijden = $this->maaltijdenService->getMaaltijdenVoorAgenda($from, $to);

		// Maaltijd aanmeldingen
		$mids = [];
		foreach ($maaltijden as $maaltijd) {
			$id = $maaltijd->maaltijd_id;
			$mids[$id] = $maaltijd;

			$maaltijd->gesloten = $maaltijd->gesloten ? '1' : '0';
			$result[] = $maaltijd;
		}
		$maaltijdAanmeldingen = array_keys(
			$this->maaltijdAanmeldingenRepository->getAanmeldingenVoorLid(
				$mids,
				$this->getUid()
			)
		);

		// Sorteren
		usort($result, [AgendaRepository::class, 'vergelijkAgendeerbaars']);

		$agenda = [
			'events' => $result,
			'joined' => [
				'maaltijden' => $maaltijdAanmeldingen,
				'activiteiten' => $activiteitAanmeldingen,
			],
		];

		return new JsonResponse(['data' => $agenda]);
	}
}
