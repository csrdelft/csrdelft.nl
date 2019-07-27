<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\controller\framework\QueryParamTrait;
use CsrDelft\model\agenda\AgendaModel;
use CsrDelft\model\agenda\AgendaVerbergenModel;
use CsrDelft\model\entity\agenda\AgendaItem;
use CsrDelft\model\entity\agenda\Agendeerbaar;
use CsrDelft\model\groepen\ActiviteitenModel;
use CsrDelft\model\maalcie\CorveeTakenModel;
use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\model\ProfielModel;
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
		$datum = strtotime($jaar . '-' . $maand . '-01');

		// URL voor vorige maand
		$urlVorige = '/agenda/maand/';
		if ($maand == 1) {
			$urlVorige .= ($jaar - 1) . '/12';
		} else {
			$urlVorige .= $jaar . '/' . ($maand - 1);
		}

		// URL voor volgende maand
		$urlVolgende = '/agenda/maand/';
		if ($maand == 12) {
			$urlVolgende .= ($jaar + 1) . '/1';
		} else {
			$urlVolgende .= $jaar . '/' . ($maand + 1);
		}

		return view('agenda.maand', [
			'datum' => $datum,
			'maand' => $maand,
			'jaar' => $jaar,
			'weken' => $this->model->getItemsByMaand($jaar, $maand),
			'urlVorige' => $urlVorige,
			'prevMaand' => strftime('%B', strtotime('-1 Month', $datum)),
			'urlVolgende' => $urlVolgende,
			'nextMaand' => strftime('%B', strtotime('+1 Month', $datum)),
		]);
	}

	public function ical() {
		header('Content-Type: text/calendar; charset=UTF-8');
		return view('agenda.icalendar', [
			'items' => $this->model->getICalendarItems(),
			'published' => str_replace('-', '', str_replace(':', '', str_replace('+00:00', 'Z', gmdate('c')))),
		]);
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
		$item = $this->model->nieuw($datum);
		$form = new AgendaItemForm($item, 'toevoegen'); // fetches POST values itself
		if ($form->validate()) {
			$item->item_id = (int)$this->model->create($item);
			if ($datum === 'doorgaan') {
				$_POST = []; // clear post values of previous input
				setMelding('Toegevoegd: ' . $item->titel . ' (' . $item->begin_moment . ')', 1);
				$item->item_id = null;
				return new AgendaItemForm($item, 'toevoegen'); // fetches POST values itself
			} else {
				return view('agenda.maand_item', ['item' => $item]);
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
			return view('agenda.maand_item', ['item' => $item]);
		} else {
			return $form;
		}
	}

	public function verwijderen($aid) {
		$item = $this->model->getAgendaItem((int)$aid);
		if (!$item || !$item->magBeheren()) {
			throw new CsrToegangException();
		}
		$this->model->delete($item);
		return view('agenda.delete', ['uuid' => $item->getUUID()]);
	}

	public function verbergen($refuuid = null) {
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
		if (!$item) {
			throw new CsrToegangException();
		}
		/**
		 * @var Agendeerbaar $agendaitem
		 */
		$agendaitem = $item;
		AgendaVerbergenModel::instance()->toggleVerbergen($agendaitem);
		return view('agenda.maand_item', ['item' => $item]);
	}

}
