<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\controller\framework\QueryParamTrait;
use CsrDelft\model\agenda\AgendaModel;
use CsrDelft\model\agenda\AgendaVerbergenModel;
use CsrDelft\model\entity\agenda\AgendaItem;
use CsrDelft\model\entity\agenda\Agendeerbaar;
use CsrDelft\model\entity\groepen\Activiteit;
use CsrDelft\model\entity\groepen\Ketzer;
use CsrDelft\model\entity\maalcie\Maaltijd;
use CsrDelft\model\entity\profiel\Profiel;
use CsrDelft\model\groepen\ActiviteitenModel;
use CsrDelft\model\maalcie\CorveeTakenModel;
use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\agenda\AgendaItemForm;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\View;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van de agenda.
 */
class AgendaController {
	use QueryParamTrait;

	private $model;

	public function __construct() {
		$this->model = AgendaModel::instance();
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
		header('Content-Type: text/calendar; charset=UTF-8');
		return fix_ical(view('agenda.icalendar', [
			'items' => $this->model->getICalendarItems(),
			'published' => $this->icalDate(),
		]));
	}

	public function export($uuid) {
		header('Content-Type: text/calendar; charset=UTF-8');
		return fix_ical(view('agenda.icalendar_export', [
			'item' => $this->getAgendaItemByUuid($uuid),
			'published' => $this->icalDate(),
		]));
	}

	public function zoeken() {
		if (!$this->hasParam('q')) {
			throw new CsrToegangException();
		}
		$query = '%' . $this->getParam('q') . '%';
		$limit = 5;
		if ($this->hasParam('limit')) {
			$limit = (int)$this->getParam('limit');
		}
		$van = date('Y-m-d');
		$tot = date('Y-m-d', strtotime('+6 months'));
		/** @var AgendaItem[] $items */
		$items = $this->model->find('eind_moment >= ? AND begin_moment <= ? AND (titel LIKE ? OR beschrijving LIKE ? OR locatie LIKE ?)', [$van, $tot, $query, $query, $query], null, 'begin_moment ASC, titel ASC', $limit);
		$result = [];
		foreach ($items as $item) {
			$begin = $item->getBeginMoment();
			$d = date('d', $begin);
			$m = date('m', $begin);
			$y = date('Y', $begin);
			if ($item->getUrl()) {
				$url = $item->getUrl();
			} else {
				$url = '/agenda/maand/' . $y . '/' . $m . '/' . $d . '#dag-' . $y . '-' . $m . '-' . $d;
			}
			$result[] = array(
				'url' => $url,
				'label' => $d . ' ' . strftime('%b', $begin) . ' ' . $y,
				'value' => $item->getTitel()
			);
		}
		return new JsonResponse($result);
	}

	public function courant() {
		$beginMoment = strtotime(date('Y-m-d'));
		$eindMoment = strtotime('next saturday', strtotime('+2 weeks', $beginMoment));
		$items = $this->model->getAllAgendeerbaar($beginMoment, $eindMoment, false, false);
		return view('agenda.courant', ['items' => $items]);
	}

	public function toevoegen($datum = null) {
		if (!LoginModel::mag(P_AGENDA_ADD) && !LoginModel::getProfiel()->verticaleleider) {
			throw new CsrToegangException('Mag geen gebeurtenis toevoegen.');
		}

		$item = $this->model->nieuw($datum);
		if (LoginModel::getProfiel()->verticaleleider && !LoginModel::mag(P_AGENDA_ADD)) {
			$item->rechten_bekijken = 'verticale:' . LoginModel::getProfiel()->verticale;
		}
		$form = new AgendaItemForm($item, 'toevoegen'); // fetches POST values itself
		if ($form->validate()) {
			if (LoginModel::getProfiel()->verticaleleider && !LoginModel::mag(P_AGENDA_ADD)) {
				$item->rechten_bekijken = 'verticale:' . LoginModel::getProfiel()->verticale;
			}
			$item->item_id = (int)$this->model->create($item);
			if ($datum === 'doorgaan') {
				$_POST = []; // clear post values of previous input
				setMelding('Toegevoegd: ' . $item->titel . ' (' . $item->begin_moment . ')', 1);
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
		$item = $this->model->getAgendaItem((int)$aid);
		if (!$item || !$item->magBeheren()) {
			throw new CsrToegangException();
		}
		$form = new AgendaItemForm($item, 'bewerken'); // fetches POST values itself
		if ($form->validate()) {
			$this->model->update($item);
			return new JsonResponse(true);
		} else {
			return $form;
		}
	}

	public function verplaatsen($uuid) {
		$item = $this->getAgendaItemByUuid($uuid);

		if (!$item || !$item instanceof AgendaItem) throw new CsrGebruikerException('Kan alleen AgendaItem verplaatsen');

		if (!$item->magBeheren()) throw new CsrToegangException();

		$item->begin_moment = $this->getPost('begin_moment');
		$item->eind_moment = $this->getPost('eind_moment');

		$this->model->update($item);

		return new JsonResponse(true);
	}

	public function verwijderen($aid) {
		$item = $this->model->getAgendaItem((int)$aid);
		if (!$item || !$item->magBeheren()) {
			throw new CsrToegangException();
		}
		$this->model->delete($item);
		return new JsonResponse(true);
	}

	public function verbergen($refuuid = null) {
		$item = $this->getAgendaItemByUuid($refuuid);
		if (!$item) {
			throw new CsrToegangException();
		}
		AgendaVerbergenModel::instance()->toggleVerbergen($item);
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
				$item = ProfielModel::instance()->retrieveByUUID($refuuid);
				break;

			case 'maaltijd':
				$item = MaaltijdenModel::instance()->retrieveByUUID($refuuid);
				break;

			case 'corveetaak':
				$item = CorveeTakenModel::instance()->retrieveByUUID($refuuid);
				break;

			case 'activiteit':
				$item = ActiviteitenModel::instance()->retrieveByUUID($refuuid);
				break;

			case 'agendaitem':
				$item = AgendaModel::instance()->retrieveByUUID($refuuid);
				break;

			default:
				throw new CsrException('invalid UUID');
		}
		return $item;
	}

	public function feed() {
		$startMoment = strtotime(filter_input(INPUT_GET, 'start'));
		$eindMoment = strtotime(filter_input(INPUT_GET, 'end'));
		$events = $this->model->getAllAgendeerbaar($startMoment, $eindMoment);

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
		return view('agenda.details', ['item' => $this->getAgendaItemByUuid($uuid)]);
	}

	/**
	 * @return mixed
	 */
	public function icalDate() {
		return str_replace('-', '', str_replace(':', '', str_replace('+00:00', 'Z', gmdate('c'))));
	}

}
