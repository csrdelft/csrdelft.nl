<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
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
use CsrDelft\service\security\LoginService;
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
	/**
	 * @var AgendaRepository
	 */
	private $agendaRepository;
	/**
	 * @var AgendaVerbergenRepository
	 */
	private $agendaVerbergenRepository;
	/**
	 * @var ActiviteitenRepository
	 */
	private $activiteitenRepository;
	/**
	 * @var CorveeTakenRepository
	 */
	private $corveeTakenRepository;
	/**
	 * @var MaaltijdenRepository
	 */
	private $maaltijdenRepository;
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;

	public function __construct(
		AgendaRepository $agendaRepository,
		AgendaVerbergenRepository $agendaVerbergenRepository,
		ActiviteitenRepository $activiteitenRepository,
		CorveeTakenRepository $corveeTakenRepository,
		MaaltijdenRepository $maaltijdenRepository,
		ProfielRepository $profielRepository
	) {
		$this->agendaRepository = $agendaRepository;
		$this->agendaVerbergenRepository = $agendaVerbergenRepository;
		$this->activiteitenRepository = $activiteitenRepository;
		$this->corveeTakenRepository = $corveeTakenRepository;
		$this->maaltijdenRepository = $maaltijdenRepository;
		$this->profielRepository = $profielRepository;
	}

	/**
	 * Maandoverzicht laten zien.
	 * @param int $jaar
	 * @param int $maand
	 * @return Response
	 * @Route(
	 *   "/agenda/{jaar}/{maand}",
	 *   methods={"GET"},
	 *   defaults={"jaar": null, "maand": null},
	 *   requirements={"jaar": "\d+", "maand": "\d+"}
	 * )
	 * @Auth(P_AGENDA_READ)
	 */
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
	 * @Route("/agenda/ical/{private_auth_token}/csrdelft.ics", methods={"GET"})
	 * @IsGranted("ROLE_LOGGED_IN")
	 */
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
	 * @Route("/agenda/export/{uuid}.ics", methods={"GET"}, requirements={"uuid": ".+"})
	 * @Auth(P_LOGGED_IN)
	 */
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
	 * @Route("/agenda/zoeken", methods={"GET"})
	 * @Auth(P_AGENDA_READ)
	 */
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
			$d = date('d', $begin);
			$m = date('m', $begin);
			$y = date('Y', $begin);
			if ($item->getUrl()) {
				$url = $item->getUrl();
			} else {
				$url = '/agenda/' . $y . '/' . $m . '#dag-' . $y . '-' . $m . '-' . $d;
			}
			$result[] = [
				'icon' => Icon::getTag('calendar'),
				'url' => $url,
				'label' => $d . ' ' . strftime('%b', $begin) . ' ' . $y,
				'value' => $item->getTitel(),
			];
		}
		return new JsonResponse($result);
	}

	/**
	 * @param BbToProsemirror $bbToProsemirror
	 * @return Response
	 * @Route("/agenda/courant", methods={"POST"})
	 * @Auth(P_MAIL_COMPOSE)
	 */
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
	 * @Route("/agenda/toevoegen/{datum}", methods={"POST"}, defaults={"datum": null})
	 * @Auth(P_LOGGED_IN)
	 */
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
				setMelding(
					'Toegevoegd: ' .
						$item->titel .
						' (' .
						date_format_intl($item->begin_moment, DATETIME_FORMAT) .
						')',
					1
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
	 * @Route("/agenda/bewerken/{aid}", methods={"POST"}, requirements={"aid": "\d+"})
	 * @Auth(P_LOGGED_IN)
	 */
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
	 * @Route("/agenda/verplaatsen/{uuid}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
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
	 * @Route("/agenda/verwijderen/{aid}", methods={"POST"}, requirements={"aid": "\d+"})
	 * @Auth(P_AGENDA_MOD)
	 */
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
	 * @Route("/agenda/verbergen/{refuuid}", methods={"POST"}, defaults={"refuuid": null})
	 * @Auth(P_LOGGED_IN)
	 */
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
		$parts = explode('@', $refuuid, 2);
		$module = explode('.', $parts[1], 2);
		switch ($module[0]) {
			case 'csrdelft':
				$item = $this->profielRepository->retrieveByUUID($refuuid);
				break;

			case 'maaltijd':
				$item = $this->maaltijdenRepository->retrieveByUUID($refuuid);
				break;

			case 'corveetaak':
				$item = $this->corveeTakenRepository->retrieveByUUID($refuuid);
				break;

			case 'activiteit':
				$item = $this->activiteitenRepository->retrieveByUUID($refuuid);
				break;

			case 'agendaitem':
				$item = $this->agendaRepository->retrieveByUUID($refuuid);
				break;

			default:
				throw new CsrException('invalid UUID');
		}
		/** @var Agendeerbaar|null $item * */
		return $item;
	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 * @Route("/agenda/feed", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
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
				date('H:i', $event->getEindMoment()) === '23:59'
			) {
				$eind = date_create_immutable('@' . $event->getEindMoment())
					->add(new DateInterval('P1D'))
					->setTime(0, 0, 0)
					->getTimestamp();
			} else {
				$eind = $event->getEindMoment();
			}

			$eventsJson[] = [
				'title' => $event->getTitel(),
				'start' => date('c', $event->getBeginMoment()),
				'end' => date('c', $eind),
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
	 * @Route("/agenda/details/{uuid}", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
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
