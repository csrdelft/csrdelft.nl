<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\FlashType;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\entity\agenda\AgendaItem;
use CsrDelft\entity\agenda\Agendeerbaar;
use CsrDelft\entity\groepen\Activiteit;
use CsrDelft\entity\groepen\Ketzer;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\agenda\AgendaRepository;
use CsrDelft\repository\agenda\AgendaVerbergenRepository;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\groepen\ActiviteitenRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\agenda\AgendaItemForm;
use CsrDelft\view\bbcode\BbToProsemirror;
use CsrDelft\view\Icon;
use CsrDelft\view\response\IcalResponse;
use DateInterval;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van de agenda.
 */
class AgendaController extends AbstractController
{
	const SECONDEN_IN_JAAR = 31557600;

	public function __construct(
		private readonly AgendaRepository $agendaRepository,
		private readonly AgendaVerbergenRepository $agendaVerbergenRepository,
		private readonly ActiviteitenRepository $activiteitenRepository,
		private readonly CorveeTakenRepository $corveeTakenRepository,
		private readonly MaaltijdenRepository $maaltijdenRepository,
		private readonly ProfielRepository $profielRepository
	) {
	}

	/**
	 * Maandoverzicht laten zien.
	 * @param int $jaar
	 * @param int $maand
	 * @return Response
	 * @Auth(P_AGENDA_READ)
	 */
	#[
		Route(
			path: '/agenda/{jaar}/{maand}',
			methods: ['GET'],
			defaults: ['jaar' => null, 'maand' => null],
			requirements: ['jaar' => '\d+', 'maand' => '\d+']
		)
	]
	public function maand($jaar = 0, $maand = 0): Response
	{
		$jaar = intval($jaar);
		if ($jaar < 1970 || $jaar > 2100) {
			$jaar = date('Y');
		}
		$maand = intval($maand);
		if ($maand < 1 || $maand > 12) {
			$maand = date('n');
		}

		return $this->render('agenda/maand.html.twig', [
			'maand' => $maand,
			'jaar' => $jaar,
			'creator' =>
				$this->mag(P_AGENDA_ADD) || $this->getProfiel()->verticaleleider,
		]);
	}

	/**
	 * @return Response
	 * @IsGranted("ROLE_LOGGED_IN")
	 */
	#[
		Route(
			path: '/agenda/ical/{private_auth_token}/csrdelft.ics',
			methods: ['GET']
		)
	]
	public function ical(): Response
	{
		return $this->render(
			'agenda/icalendar.ical.twig',
			[
				'items' => $this->agendaRepository->getICalendarItems(),
				'published' => $this->icalDate(),
			],
			new IcalResponse()
		);
	}

	/**
	 * @param $uuid
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[
		Route(
			path: '/agenda/export/{uuid}.ics',
			methods: ['GET'],
			requirements: ['uuid' => '.+']
		)
	]
	public function export($uuid): Response
	{
		return $this->render(
			'agenda/icalendar.ical.twig',
			[
				'items' => [$this->getAgendaItemByUuid($uuid)],
				'published' => $this->icalDate(),
			],
			new IcalResponse()
		);
	}

	/**
	 * @param Request $request
	 * @param null $zoekterm
	 * @return JsonResponse
	 * @Auth(P_AGENDA_READ)
	 */
	#[Route(path: '/agenda/zoeken', methods: ['GET'])]
	public function zoeken(Request $request, $zoekterm = null): JsonResponse
	{
		if (!$zoekterm && !$request->query->has('q')) {
			throw $this->createAccessDeniedException();
		}

		if (!$zoekterm) {
			$zoekterm = $request->query->get('q');
		}

		$limit = 5;
		if ($request->query->has('limit')) {
			$limit = $request->query->getInt('limit');
		}
		$items = $this->agendaRepository->zoeken(
			date_create_immutable(),
			date_create_immutable('+6 months'),
			$zoekterm,
			$limit
		);
		$result = [];
		foreach ($items as $item) {
			$begin = $item->getBeginMoment();
			$d = $begin->format('d');
			$m = $begin->format('m');
			$y = $begin->format('Y');
			if ($item->getUrl()) {
				$url = $item->getUrl();
			} else {
				$url = '/agenda/' . $y . '/' . $m . '#dag-' . $y . '-' . $m . '-' . $d;
			}
			$result[] = [
				'icon' => Icon::getTag('calendar'),
				'url' => $url,
				'label' =>
					$d . ' ' . DateUtil::dateFormatIntl($begin, 'LLLL') . ' ' . $y,
				'value' => $item->getTitel(),
			];
		}
		return new JsonResponse($result);
	}

	/**
	 * @param BbToProsemirror $bbToProsemirror
	 * @return Response
	 * @Auth(P_MAIL_COMPOSE)
	 */
	#[Route(path: '/agenda/courant', methods: ['POST'])]
	public function courant(BbToProsemirror $bbToProsemirror)
	{
		$items = $this->agendaRepository->getAllAgendeerbaar(
			date_create_immutable(),
			date_create_immutable('next saturday + 2 weeks'),
			false,
			false
		);
		return new JsonResponse(
			$bbToProsemirror->toProseMirrorFragment(
				$this->renderView('agenda/courant.html.twig', ['items' => $items])
			)
		);
	}

	/**
	 * @param Request $request
	 * @param null $datum
	 * @return JsonResponse|Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[
		Route(
			path: '/agenda/toevoegen/{datum}',
			methods: ['POST'],
			defaults: ['datum' => null]
		)
	]
	public function toevoegen(Request $request, $datum = null)
	{
		$profiel = $this->getProfiel();
		if (!$this->mag(P_AGENDA_ADD) && !$profiel->verticaleleider) {
			throw $this->createAccessDeniedException(
				'Mag geen gebeurtenis toevoegen.'
			);
		}

		$item = $this->agendaRepository->nieuw(
			$request->request->get('begin_moment'),
			$request->request->get('eind_moment')
		);
		if ($profiel->verticaleleider && !$this->mag(P_AGENDA_ADD)) {
			$item->rechten_bekijken = 'verticale:' . $profiel->verticale;
		}
		$form = $this->createFormulier(AgendaItemForm::class, $item, [
			'actie' => 'toevoegen',
		]);
		$form->handleRequest($request);
		if ($form->validate()) {
			if ($profiel->verticaleleider && !$this->mag(P_AGENDA_ADD)) {
				$item->rechten_bekijken = 'verticale:' . $profiel->verticale;
			}
			$this->agendaRepository->save($item);
			if ($datum === 'doorgaan') {
				$_POST = []; // clear post values of previous input
				$this->addFlash(
					FlashType::SUCCESS,
					'Toegevoegd: ' .
						$item->titel .
						' (' .
						DateUtil::dateFormatIntl(
							$item->begin_moment,
							DateUtil::DATETIME_FORMAT
						) .
						')'
				);
				$item->item_id = null;
				return new Response(
					$this->createFormulier(AgendaItemForm::class, $item, [
						'actie' => 'toevoegen',
					])->createModalView()
				);
			} else {
				return new JsonResponse(true);
			}
		} else {
			return new Response($form->createModalView());
		}
	}

	/**
	 * @param Request $request
	 * @param $aid
	 * @return JsonResponse|Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[
		Route(
			path: '/agenda/bewerken/{aid}',
			methods: ['POST'],
			requirements: ['aid' => '\d+']
		)
	]
	public function bewerken(Request $request, $aid)
	{
		$item = $this->agendaRepository->getAgendaItem((int) $aid);
		if (!$item || !$item->magBeheren()) {
			throw $this->createAccessDeniedException();
		}
		$form = $this->createFormulier(AgendaItemForm::class, $item, [
			'actie' => 'bewerken',
		]);
		$form->handleRequest($request);
		if ($form->validate()) {
			$this->agendaRepository->save($item);
			return new JsonResponse(true);
		} else {
			return new Response($form->createModalView());
		}
	}

	/**
	 * @param Request $request
	 * @param $uuid
	 * @return JsonResponse
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/agenda/verplaatsen/{uuid}', methods: ['POST'])]
	public function verplaatsen(Request $request, $uuid): JsonResponse
	{
		$item = $this->getAgendaItemByUuid($uuid);

		if (!$item || !$item instanceof AgendaItem) {
			throw new CsrGebruikerException('Kan alleen AgendaItem verplaatsen');
		}

		if (!$item->magBeheren()) {
			throw $this->createAccessDeniedException();
		}

		$item->begin_moment = date_create_immutable(
			$request->request->get('begin_moment')
		);
		$item->eind_moment = date_create_immutable(
			$request->request->get('eind_moment')
		);

		$this->agendaRepository->save($item);

		return new JsonResponse(true);
	}

	/**
	 * @param $aid
	 * @return JsonResponse
	 * @Auth(P_AGENDA_MOD)
	 */
	#[
		Route(
			path: '/agenda/verwijderen/{aid}',
			methods: ['POST'],
			requirements: ['aid' => '\d+']
		)
	]
	public function verwijderen($aid): JsonResponse
	{
		$item = $this->agendaRepository->getAgendaItem((int) $aid);
		if (!$item || !$item->magBeheren()) {
			throw $this->createAccessDeniedException();
		}
		$this->agendaRepository->remove($item);
		return new JsonResponse(true);
	}

	/**
	 * @param null $refuuid
	 * @return JsonResponse
	 * @Auth(P_LOGGED_IN)
	 */
	#[
		Route(
			path: '/agenda/verbergen/{refuuid}',
			methods: ['POST'],
			defaults: ['refuuid' => null]
		)
	]
	public function verbergen($refuuid = null): JsonResponse
	{
		$item = $this->getAgendaItemByUuid($refuuid);
		if (!$item) {
			throw $this->createAccessDeniedException();
		}
		$this->agendaVerbergenRepository->toggleVerbergen($item);
		return new JsonResponse(true);
	}

	/**
	 * @param $refuuid
	 * @return Agendeerbaar|null
	 */
	private function getAgendaItemByUuid($refuuid)
	{
		$parts = explode('@', (string) $refuuid, 2);
		$module = explode('.', $parts[1], 2);
		$item = match ($module[0]) {
			'csrdelft' => $this->profielRepository->retrieveByUUID($refuuid),
			'maaltijd' => $this->maaltijdenRepository->retrieveByUUID($refuuid),
			'corveetaak' => $this->corveeTakenRepository->retrieveByUUID($refuuid),
			'activiteit' => $this->activiteitenRepository->retrieveByUUID($refuuid),
			'agendaitem' => $this->agendaRepository->retrieveByUUID($refuuid),
			default => throw new CsrException('invalid UUID'),
		};
		/** @var Agendeerbaar|null $item * */
		return $item;
	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/agenda/feed', methods: ['GET'])]
	public function feed(Request $request): JsonResponse
	{
		$startMoment = date_create_immutable($request->query->get('start'));
		$eindMoment = date_create_immutable($request->query->get('end'));

		if (
			$startMoment->add(DateInterval::createFromDateString('1 year')) <
			$eindMoment
		) {
			// Om de gare logica omtrent verjaardagen te laten werken
			throw new CsrGebruikerException(
				'Verschil tussen start en eind mag niet groter zijn dan een jaar.'
			);
		}

		$events = $this->agendaRepository->getAllAgendeerbaar(
			$startMoment,
			$eindMoment
		);

		$eventsJson = [];
		foreach ($events as $event) {
			$backgroundColor = '#214AB0';
			if ($event instanceof Profiel) {
				$backgroundColor = '#BD135E';
			} elseif ($event instanceof Maaltijd) {
				$backgroundColor = '#731CC7';
			} elseif ($event instanceof Activiteit) {
				$backgroundColor = '#1CC7BC';
			} elseif ($event instanceof Ketzer) {
				$backgroundColor = '#1ABD2C';
			}

			// Zet eindmoment naar dag erna als activiteit tot 23:59 duurt en allDay is
			if (
				$event->isHeledag() &&
				$event->getEindMoment()->format('H:i') === '23:59'
			) {
				$eind = $event
					->getEindMoment()
					->add(new DateInterval('P1D'))
					->setTime(0, 0, 0);
			} else {
				$eind = $event->getEindMoment();
			}

			$eventsJson[] = [
				'title' => $event->getTitel(),
				'start' => $event->getBeginMoment()->format('c'),
				'end' => $eind->format('c'),
				'allDay' => $event->isHeledag(),
				'id' => $event->getUUID(),
				'textColor' => '#FFF',
				'backgroundColor' => $backgroundColor,
				'borderColor' => $backgroundColor,
				'description' => $event->getBeschrijving(),
				'location' => $event->getLocatie(),
				'editable' => $event instanceof AgendaItem && $event->magBeheren(),
			];
		}

		return new JsonResponse($eventsJson);
	}

	/**
	 * @param $uuid
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/agenda/details/{uuid}', methods: ['GET'])]
	public function details($uuid): Response
	{
		$jaar = filter_input(INPUT_GET, 'jaar', FILTER_SANITIZE_NUMBER_INT);

		if ($jaar) {
			$GLOBALS['agenda_jaar'] = $jaar;
		}
		$item = $this->getAgendaItemByUuid($uuid);

		if (!$item) {
			throw $this->createNotFoundException();
		}

		return $this->render('agenda/details.html.twig', [
			'item' => $item,
			'verborgen' => $this->agendaVerbergenRepository->isVerborgen($item),
		]);
	}

	/**
	 * @return mixed
	 */
	public function icalDate()
	{
		return str_replace(
			'-',
			'',
			str_replace(':', '', str_replace('+00:00', 'Z', gmdate('c')))
		);
	}
}
