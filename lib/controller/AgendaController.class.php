<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrException;
use CsrDelft\controller\framework\AclController;
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
 *
 * @property AgendaModel $model
 */
class AgendaController extends AclController {

	public function __construct($query) {
		parent::__construct($query, AgendaModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'maand' => 'P_AGENDA_READ',
				'ical' => 'P_PUBLIC',
				'zoeken' => 'P_AGENDA_READ'
			);
		} else {
			$this->acl = array(
				'courant' => 'P_MAIL_COMPOSE',
				'toevoegen' => 'P_AGENDA_ADD',
				'bewerken' => 'P_AGENDA_MOD',
				'verwijderen' => 'P_AGENDA_MOD',
				'verbergen' => 'P_LOGGED_IN',
				'tonen' => 'P_LOGGED_IN'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'maand';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		if ($this->action === 'csrdelft.ics') {
			$this->action = 'ical';
		}
		parent::performAction($this->getParams(3));
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
		$this->view = new CsrLayoutPage($body);
	}

	public function ical() {
		header('Content-Type: text/calendar; charset=UTF-8');
		$this->view = new AgendaICalendarView($this->model);
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
		$this->view = new JsonResponse($result);
	}

	public function courant() {
		$this->view = new AgendaCourantView($this->model, 2);
	}

	public function toevoegen($datum = null) {
		$item = $this->model->nieuw($datum);
		$form = new AgendaItemForm($item, $this->action); // fetches POST values itself
		if ($form->validate()) {
			$item->item_id = (int)$this->model->create($item);
			if ($datum === 'doorgaan') {
				$_POST = array(); // clear post values of previous input
				setMelding('Toegevoegd: ' . $item->titel . ' (' . $item->begin_moment . ')', 1);
				$item->item_id = null;
				$this->view = new AgendaItemForm($item, $this->action); // fetches POST values itself
			} else {
				$this->view = new AgendeerbaarMaandView($item);
			}
		} else {
			$this->view = $form;
		}
	}

	public function bewerken($aid) {
		$item = $this->model->getAgendaItem((int)$aid);
		if (!$item OR !$item->magBeheren()) {
			$this->exit_http(403);
			return;
		}
		$form = new AgendaItemForm($item, $this->action); // fetches POST values itself
		if ($form->validate()) {
			$this->model->update($item);
			$this->view = new AgendeerbaarMaandView($item);
		} else {
			$this->view = $form;
		}
	}

	public function verwijderen($aid) {
		$item = $this->model->getAgendaItem((int)$aid);
		if (!$item OR !$item->magBeheren()) {
			$this->exit_http(403);
			return;
		}
		$this->model->delete($item);
		$this->view = new AgendaItemDeleteView($item->item_id);
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
			$this->exit_http(403);
			return;
		}
		/**
		 * @var Agendeerbaar $agendaitem
		 */
		$agendaitem = $item;
		AgendaVerbergenModel::instance()->toggleVerbergen($agendaitem);
		$this->view = new AgendeerbaarMaandView($agendaitem);
	}

}
