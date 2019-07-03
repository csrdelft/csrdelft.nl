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
use CsrDelft\view\agenda\AgendaCourantView;
use CsrDelft\view\agenda\AgendaICalendarView;
use CsrDelft\view\agenda\AgendaItemDeleteView;
use CsrDelft\view\agenda\AgendaItemForm;
use CsrDelft\view\agenda\AgendaMaandView;
use CsrDelft\view\agenda\AgendeerbaarMaandView;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\JsonResponse;

/**
 * ApiAgendaController.class.php
 *
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
	 */
	public function maand($jaar = 0, $maand = 0) {
		$jaar = intval($jaar);
		if ($jaar < 1970 OR $jaar > 2100) {
			$jaar = date('Y');
		}
		$maand = intval($maand);
		if ($maand < 1 OR $maand > 12) {
			$maand = date('n');
		}
		$body = new AgendaMaandView($this->model, $jaar, $maand);
		return new CsrLayoutPage($body);
	}

	public function ical() {
		header('Content-Type: text/calendar; charset=UTF-8');
		return new AgendaICalendarView($this->model);
	}

	public function zoeken() {
		if (!$this->hasParam('q')) {
			$this->exit_http(403);
		}
		$query = '%' . $this->getParam('q') . '%';
		$limit = 5;
		if ($this->hasParam('limit')) {
			$limit = (int)$this->getParam('limit');
		}
		$van = date('Y-m-d');
		$tot = date('Y-m-d', strtotime('+6 months'));
		/** @var AgendaItem[] $items */
		$items = $this->model->find('eind_moment >= ? AND begin_moment <= ? AND (titel LIKE ? OR beschrijving LIKE ? OR locatie LIKE ?)', array($van, $tot, $query, $query, $query), null, 'begin_moment ASC, titel ASC', $limit);
		$result = array();
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
		return new AgendaCourantView($this->model, 2);
	}

	public function toevoegen($datum = null) {
		$item = $this->model->nieuw($datum);
		$form = new AgendaItemForm($item, 'toevoegen'); // fetches POST values itself
		if ($form->validate()) {
			$item->item_id = (int)$this->model->create($item);
			if ($datum === 'doorgaan') {
				$_POST = array(); // clear post values of previous input
				setMelding('Toegevoegd: ' . $item->titel . ' (' . $item->begin_moment . ')', 1);
				$item->item_id = null;
				return new AgendaItemForm($item, 'toevoegen'); // fetches POST values itself
			} else {
				return new AgendeerbaarMaandView($item);
			}
		} else {
			return $form;
		}
	}

	public function bewerken($aid) {
		$item = $this->model->getAgendaItem((int)$aid);
		if (!$item OR !$item->magBeheren()) {
			throw new CsrToegangException();
		}
		$form = new AgendaItemForm($item, 'bewerken'); // fetches POST values itself
		if ($form->validate()) {
			$this->model->update($item);
			return new AgendeerbaarMaandView($item);
		} else {
			return $form;
		}
	}

	public function verwijderen($aid) {
		$item = $this->model->getAgendaItem((int)$aid);
		if (!$item OR !$item->magBeheren()) {
			throw new CsrToegangException();
		}
		$this->model->delete($item);
		return new AgendaItemDeleteView($item);
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
		return new AgendeerbaarMaandView($agendaitem);
	}

}
