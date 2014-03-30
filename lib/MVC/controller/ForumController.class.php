<?php

require_once 'MVC/model/ForumModel.class.php';
require_once 'MVC/view/ForumView.class.php';

/**
 * ForumController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller van het forum.
 */
class ForumController extends Controller {

	public function __construct($query) {
		parent::__construct($query, ForumModel::instance());
		$this->action = 'start';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
			if ($this->action === 'calendar.ics') {
				$this->action = 'icalendar';
				header("Content-Type: text/calendar");
				header('Content-Disposition: attachment; filename="calendar.ics"');
			}
		}
		$this->performAction($this->getParams(3));
	}

	protected function hasPermission() {
		return true; // check permissions for actions on forum-categorie & forum-deel
	}

	/**
	 * Overzicht met categorien en forumdelen laten zien.
	 */
	public function start() {
		$body = new ForumView($this->model->getForum());
		$this->view = new CsrLayoutPage($body);
		$this->view->addStylesheet('forum.css');
	}

//TODO

	public function toevoegen($datum = '', $doorgaan = true) {
		$item = $this->model->newForumItem($datum);
		$this->view = new ForumItemFormView($item, $this->action); // fetches POST values itself
		if ($doorgaan AND $this->view->validate()) {
			$id = $this->model->create($item);
			$item->item_id = (int) $id;
			setMelding('Toegevoegd: ' . $item->titel . ' (' . $item->begin_moment . ')', 1);
			$this->view = new ForumItemMaandView($item);
			return true; // voor doorgaan
		}
	}

	public function doorgaan() {
		$this->action = 'toevoegen';
		if ($this->toevoegen()) {
			$item = $this->view->getModel();
			$_POST['datum_dag'] = date('d', $item->getEindMoment() + 60); // spring naar volgende dag bij 23:59
			$this->toevoegen('', false);
		}
	}

	public function bewerken($aid) {
		$item = $this->model->getForumItem($aid);
		$this->view = new ForumItemFormView($item, $this->action); // fetches POST values itself
		if ($this->view->validate()) {
			$rowcount = $this->model->update($item);
			if ($rowcount > 0) {
				//setMelding('Bijgewerkt', 1);
			} else {
				//setMelding('Geen wijzigingen', 0);
			}
			$this->view = new ForumItemMaandView($item);
		}
	}

	public function verwijderen($aid) {
		if ($this->model->removeForumItem($aid)) {
			//setMelding('Verwijderd', 1);
			$this->view = new ForumItemDeleteView($aid);
		}
	}

}
