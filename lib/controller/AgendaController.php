<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\entity\agenda\AgendaItem;
use CsrDelft\entity\agenda\Agendeerbaar;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\groepen\Activiteit;
use CsrDelft\model\entity\groepen\Ketzer;
use CsrDelft\model\groepen\ActiviteitenModel;
use CsrDelft\model\maalcie\CorveeTakenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\agenda\AgendaRepository;
use CsrDelft\repository\agenda\AgendaVerbergenRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\agenda\AgendaItemForm;
use CsrDelft\view\Icon;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\response\IcalResponse;
use CsrDelft\view\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van de agenda.
 */
class AgendaController {
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
	 * @var ActiviteitenModel
	 */
	private $activiteitenModel;
	/**
	 * @var CorveeTakenModel
	 */
	private $corveeTakenModel;
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
        ActiviteitenModel $activiteitenModel,
        CorveeTakenModel $corveeTakenModel,
        MaaltijdenRepository $maaltijdenRepository,
        ProfielRepository $profielRepository
	) {
		$this->agendaRepository = $agendaRepository;
		$this->agendaVerbergenRepository = $agendaVerbergenRepository;
		$this->activiteitenModel = $activiteitenModel;
		$this->corveeTakenModel = $corveeTakenModel;
		$this->maaltijdenRepository = $maaltijdenRepository;
		$this->profielRepository = $profielRepository;
	}

	/**
	 * Maandoverzicht laten zien.
	 * @param int $jaar
	 * @param int $maand
	 * @return View
	 */
	public function maand($jaar = 0, $maand = 0) {
		$jaar = intval($jaar);
		if ($jaar < 1970 || $jaar > 2100) {
			$jaar = date('Y');
		}
		$maand = intval($maand);
		if ($maand < 1 || $maand > 12) {
			$maand = date('n');
		}

		return view('agenda.maand', [
			'maand' => $maand,
			'jaar' => $jaar,
			'creator' => LoginModel::mag(P_AGENDA_ADD) || LoginModel::getProfiel()->verticaleleider,
		]);
	}

	public function ical() {
		return new IcalResponse(view('agenda.icalendar', [
			'items' => $this->agendaRepository->getICalendarItems(),
			'published' => $this->icalDate(),
		])->toString());
	}

	public function export($uuid) {
		return new IcalResponse(view('agenda.icalendar', [
			'items' => [$this->getAgendaItemByUuid($uuid)],
			'published' => $this->icalDate(),
		])->toString());
	}

	public function zoeken(Request $request, $zoekterm = null) {
		if (!$zoekterm && !$request->query->has('q')) {
			throw new CsrToegangException();
		}

		if (!$zoekterm) {
			$zoekterm = $request->query->get('q');
		}

		$limit = 5;
		if ($request->query->has('limit')) {
			$limit = $request->query->getInt('limit');
		}
		/** @var AgendaItem[] $items */
		$items = $this->agendaRepository->zoeken(date_create_immutable(), date_create_immutable('+6 months'), $zoekterm, $limit);
		$result = [];
		foreach ($items as $item) {
			$begin = $item->getBeginMoment();
			$d = date('d', $begin);
			$m = date('m', $begin);
			$y = date('Y', $begin);
			if ($item->getUrl()) {
				$url = $item->getUrl();
			} else {
				$url = '/agenda/maand/' . $y . '/' . $m . '#dag-' . $y . '-' . $m . '-' . $d;
			}
			$result[] = array(
				'icon' => Icon::getTag('calendar'),
				'url' => $url,
				'label' => $d . ' ' . strftime('%b', $begin) . ' ' . $y,
				'value' => $item->getTitel()
			);
		}
		return new JsonResponse($result);
	}

	public function courant() {
		$items = $this->agendaRepository->getAllAgendeerbaar(date_create_immutable(), date_create_immutable('next saturday + 2 weeks'), false, false);
		return view('agenda.courant', ['items' => $items]);
	}

	public function toevoegen($datum = null) {
		if (!LoginModel::mag(P_AGENDA_ADD) && !LoginModel::getProfiel()->verticaleleider) {
			throw new CsrToegangException('Mag geen gebeurtenis toevoegen.');
		}

		$item = $this->agendaRepository->nieuw($datum);
		if (LoginModel::getProfiel()->verticaleleider && !LoginModel::mag(P_AGENDA_ADD)) {
			$item->rechten_bekijken = 'verticale:' . LoginModel::getProfiel()->verticale;
		}
		$form = new AgendaItemForm($item, 'toevoegen'); // fetches POST values itself
		if ($form->validate()) {
			if (LoginModel::getProfiel()->verticaleleider && !LoginModel::mag(P_AGENDA_ADD)) {
				$item->rechten_bekijken = 'verticale:' . LoginModel::getProfiel()->verticale;
			}
			$item->item_id = (int)$this->agendaRepository->create($item);
			if ($datum === 'doorgaan') {
				$_POST = []; // clear post values of previous input
				setMelding('Toegevoegd: ' . $item->titel . ' (' . date_format_intl($item->begin_moment, DATETIME_FORMAT) . ')', 1);
				$item->item_id = null;
				return new AgendaItemForm($item, 'toevoegen'); // fetches POST values itself
			} else {
				return new JsonResponse(true);
			}
		} else {
			return $form;
		}
	}

	public function bewerken($aid) {
		$item = $this->agendaRepository->getAgendaItem((int)$aid);
		if (!$item || !$item->magBeheren()) {
			throw new CsrToegangException();
		}
		$form = new AgendaItemForm($item, 'bewerken'); // fetches POST values itself
		if ($form->validate()) {
			$this->agendaRepository->update($item);
			return new JsonResponse(true);
		} else {
			return $form;
		}
	}

	public function verplaatsen(Request $request, $uuid) {
		$item = $this->getAgendaItemByUuid($uuid);

		if (!$item || !$item instanceof AgendaItem) throw new CsrGebruikerException('Kan alleen AgendaItem verplaatsen');

		if (!$item->magBeheren()) throw new CsrToegangException();

		$item->begin_moment = date_create_immutable($request->request->get('begin_moment'));
		$item->eind_moment = date_create_immutable($request->request->get('eind_moment'));

		$this->agendaRepository->update($item);

		return new JsonResponse(true);
	}

	public function verwijderen($aid) {
		$item = $this->agendaRepository->getAgendaItem((int)$aid);
		if (!$item || !$item->magBeheren()) {
			throw new CsrToegangException();
		}
		$this->agendaRepository->delete($item);
		return new JsonResponse(true);
	}

	public function verbergen($refuuid = null) {
		$item = $this->getAgendaItemByUuid($refuuid);
		if (!$item) {
			throw new CsrToegangException();
		}
		$this->agendaVerbergenRepository->toggleVerbergen($item);
		return new JsonResponse(true);
	}

	/**
	 * @param $refuuid
	 * @return Agendeerbaar|false
	 */
	private function getAgendaItemByUuid($refuuid) {
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
				$item = $this->corveeTakenModel->retrieveByUUID($refuuid);
				break;

			case 'activiteit':
				$item = $this->activiteitenModel->retrieveByUUID($refuuid);
				break;

			case 'agendaitem':
				$item = $this->agendaRepository->retrieveByUUID($refuuid);
				break;

			default:
				throw new CsrException('invalid UUID');
		}
		/** @var Agendeerbaar|false $item **/
		return $item;
	}

	public function feed(Request $request) {
		$startMoment = date_create_immutable($request->query->get('start'));
		$eindMoment = date_create_immutable($request->query->get('end'));

		if ($startMoment->add(\DateInterval::createFromDateString('1 year')) < $eindMoment) {
			// Om de gare logica omtrent verjaardagen te laten werken
			throw new CsrGebruikerException("Verschil tussen start en eind mag niet groter zijn dan een jaar.");
		}

		$events = $this->agendaRepository->getAllAgendeerbaar($startMoment, $eindMoment);

		$eventsJson = [];
		foreach ($events as $event) {

			$backgroundColor = '#214AB0';
			if ($event instanceof Profiel) {
				$backgroundColor = '#BD135E';
			} else if ($event instanceof Maaltijd) {
				$backgroundColor = '#731CC7';
			} else if ($event instanceof Activiteit) {
				$backgroundColor = '#1CC7BC';
			} else if ($event instanceof Ketzer) {
				$backgroundColor = '#1ABD2C';
			}

			$eventsJson[] = [
				'title' => $event->getTitel(),
				'start' => date('c', $event->getBeginMoment()),
				'end' => date('c', $event->getEindMoment()),
				'allDay' => $event->isHeledag(),
				'id' => $event->getUUID(),
				'textColor' => '#fff',
				'backgroundColor' => $backgroundColor,
				'borderColor' => $backgroundColor,
				'description' => $event->getBeschrijving(),
				'location' => $event->getLocatie(),
				'editable' => $event instanceof AgendaItem && $event->magBeheren(),
			];
		}

		return new JsonResponse($eventsJson);
	}

	public function details($uuid) {
		$jaar = filter_input(INPUT_GET, 'jaar', FILTER_SANITIZE_NUMBER_INT);

		if ($jaar) {
			$GLOBALS['agenda_jaar'] = $jaar;
		}
		$item = $this->getAgendaItemByUuid($uuid);

		return view('agenda.details', [
			'item' => $item,
			'verborgen' => $this->agendaVerbergenRepository->isVerborgen($item),
		]);
	}

	/**
	 * @return mixed
	 */
	public function icalDate() {
		return str_replace('-', '', str_replace(':', '', str_replace('+00:00', 'Z', gmdate('c'))));
	}

}
