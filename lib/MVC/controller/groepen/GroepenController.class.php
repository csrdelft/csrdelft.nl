<?php

require_once 'MVC/view/groepen/GroepenView.class.php';

/**
 * GroepenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor groepen.
 */
class GroepenController extends Controller {

	public function __construct($query, GroepenModel $model) {
		parent::__construct($query, $model);
		if ($this->hasParam(4)) {
			$this->action = $this->getParam(4);
		} elseif ($this->hasParam(3)) {
			$this->action = 'tonen';
		} else {
			$this->action = 'overzicht';
		}
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(3)) {
			$args[] = (int) $this->getParam(3);
			if ($this->hasParam(5)) {
				$args[] = $this->getParam(5);
			}
		}
		parent::performAction($args);
	}

	/**
	 * Check permissions & valid params in actions.
	 * 
	 * @return boolean
	 */
	protected function mag($action, $resource) {
		if (!LoginModel::mag('P_LEDEN_READ')) {
			$this->geentoegang();
		}
		switch ($action) {
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
		$this->view = new CsrLayoutPage($this->getView());
	}

	public function tonen($id) {
		$this->lijst($id);
		$this->view = new CsrLayoutPage($this->getView());
	}

	public function lijst($id) {
		$groep = $this->model->getById($id);
		$class = str_replace('Model', '', get_class($groep));
		$view = $class . 'View';
		$this->view = new $view($groep, $this->action);
	}

	public function pasfotos($id) {
		$this->lijst($id);
	}

	public function stats($id) {
		$this->lijst($id);
	}

	public function emails($id) {
		$this->lijst($id);
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

	public function aanmelden($id, $uid) {
		//TODO
	}

	public function wijzigen($id, $uid) {
		//TODO
	}

	public function afmelden($id, $uid) {
		//TODO
	}

}
