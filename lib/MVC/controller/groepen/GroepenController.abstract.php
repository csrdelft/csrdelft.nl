<?php

require_once 'MVC/view/groepen/GroepenView.class.php';

/**
 * GroepenController.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor groepen.
 */
abstract class GroepenController extends Controller {

	public function __construct($query, GroepenModel $model) {
		parent::__construct($query, $model);
		if ($this->hasParam(4)) {
			$this->action = $this->getParam(3);
		} elseif ($this->hasParam(3) AND (int) $this->getParam(3) > 0) {
			$this->action = 'tonen';
		} else {
			$this->action = 'overzicht';
		}
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(4)) {
			parent::performAction($this->getParams(4));
		} else { // zonder user-actie
			parent::performAction($this->getParams(3));
		}
		if (!$this->isPosted()) {
			$this->view = new CsrLayoutPage($this->getContent());
		}
	}

	/**
	 * Check permissions & valid params in actions.
	 * 
	 * @return boolean
	 */
	protected function hasPermission() {
		if (!LoginLid::mag('P_LEDEN_READ')) {
			$this->geentoegang();
		}
		switch ($this->action) {
			case 'overzicht':
			case 'tonen':
			case GroepTab::Lijst:
			case GroepTab::Pasfotos:
			case GroepTab::Statistiek:
			case GroepTab::Emails:
				return !$this->isPosted();

			case 'aanmaken':
			case 'bewerken':
			case 'verwijderen':
			case 'aanmelden':
			case 'wijzigen':
			case 'afmelden':
				return $this->isPosted();

			default:
				$this->action = 'overzicht';
				return true;
		}
	}

	public function overzicht() {
		$groepen = $this->model->find('status = ?', array(GroepStatus::HT));
		$class = str_replace('Model', '', get_class($this->model));
		$view = $class . 'View';
		$this->view = new $view($groepen, $class, $class . ' (h.t.)');
	}

	public function tonen($id) {
		//TODO
	}

	public function lijst($id) {
		//TODO
	}

	public function pasfotos($id) {
		//TODO
	}

	public function stats($id) {
		//TODO
	}

	public function emails($id) {
		//TODO
	}

	public function aanmaken() {
		//TODO
	}

	public function bewerken($id) {
		//TODO
	}

	public function verwijderen($id) {
		//TODO
	}

	public function aanmelden($id, $lid_id) {
		//TODO
	}

	public function wijzigen($id, $lid_id) {
		//TODO
	}

	public function afmelden($id, $lid_id) {
		//TODO
	}

}
