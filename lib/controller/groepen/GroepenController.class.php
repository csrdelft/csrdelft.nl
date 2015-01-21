<?php

require_once 'view/groepen/GroepenView.class.php';

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
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(3)) { // id
			$this->action = A::Bekijken;
			$id = (int) $this->getParam(3);
			$groep = $this->model->get($id);
			if (!$groep) {
				$this->geentoegang();
			}
			$args[] = $groep;
			if ($this->hasParam(4)) { // action
				$this->action = $this->getParam(4);
				if ($this->hasParam(5)) { // uid
					$profiel = ProfielModel::get($this->getParam(5));
					if (!$profiel) {
						$this->geentoegang();
					}
					$args[] = $profiel;
				}
			}
			if (!$groep->mag($this->action)) {
				$this->geentoegang();
			}
		} else {
			$this->action = GroepStatus::HT; // default
			$algemeen = AccessModel::get(get_class($this->model->orm), $this->action, null);
			if (!LoginModel::mag($algemeen)) {
				$this->geentoegang();
			}
		}
		parent::performAction($args);
	}

	/**
	 * Check permissions & valid params in actions.
	 * 
	 * @return boolean
	 */
	protected function mag($action, $method) {
		switch ($action) {
			case GroepStatus::FT:
			case GroepStatus::HT:
			case GroepStatus::OT:
			case A::Bekijken:
			case GroepTab::Lijst:
			case GroepTab::Pasfotos:
			case GroepTab::Statistiek:
			case GroepTab::Emails:
				return !$this->isPosted();

			case A::Aanmaken:
			case A::Wijzigen:
			case A::Verwijderen:
			case A::Aanmelden:
			case A::Afmelden:
			case A::Bewerken:
				return $this->isPosted();

			default:
				$this->action = 'overzicht';
				return true;
		}
	}

	protected function overzicht($status) {
		$groepen = $this->model->find('status = ?', array($status));
		$class = str_replace('Model', '', get_class($this->model));
		$view = $class . 'View';
		$this->view = new $view($groepen, $class, $class . ' ' . GroepStatus::getChar($status));
		$this->view = new CsrLayoutPage($this->view);
	}

	public function ft() {
		return $this->overzicht($this->action);
	}

	public function ht() {
		return $this->overzicht($this->action);
	}

	public function ot() {
		return $this->overzicht($this->action);
	}

	public function bekijken(Groep $groep) {
		$this->groeptab($groep);
		$this->view = new CsrLayoutPage($this->view);
	}

	protected function groeptab(Groep $groep) {
		$view = get_class($groep) . 'View';
		$this->view = new $view($groep, $this->action);
	}

	public function lijst(Groep $groep) {
		return $this->groeptab($groep);
	}

	public function pasfotos(Groep $groep) {
		return $this->groeptab($groep);
	}

	public function stats(Groep $groep) {
		return $this->groeptab($groep);
	}

	public function emails(Groep $groep) {
		return $this->groeptab($groep);
	}

	public function aanmaken() {
		//TODO
	}

	public function wijzigen(Groep $groep) {
		//TODO
	}

	public function verwijderen(Groep $groep) {
		//TODO
	}

	public function aanmelden(Groep $groep, Profiel $profiel) {
		//TODO
	}

	public function afmelden(Groep $groep, Profiel $profiel) {
		//TODO
	}

	public function bewerken(Groep $groep, Profiel $profiel) {
		//TODO
	}

}
