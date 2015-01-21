<?php

require_once 'view/GroepenView.class.php';

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
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(3)) { // id or action
			$this->action = $this->getParam(3);
		} else {
			$this->action = 'overzicht'; // default
		}
		if ($this->action === 'overzicht' OR $this->action === 'beheren') {
			if (!DEBUG) {
				$model = $this->model;
				$algemeen = AccessModel::get($model::orm, $this->action, null);
				if (!LoginModel::mag($algemeen)) {
					$this->geentoegang();
				}
			}
		} else {
			$id = (int) $this->action; // id
			$groep = $this->model->get($id);
			if (!$groep) {
				$this->geentoegang();
			}
			$args[] = $groep;
			if ($this->hasParam(4)) { // action
				$this->action = $this->getParam(4);
				if ($this->hasParam(5)) { // uid
					$profiel = ProfielModel::get($this->getParam(5)); // uid
					if (!$profiel) {
						$this->geentoegang();
					}
					$args[] = $profiel;
				}
			} else {
				$this->action = A::Bekijken; // default
			}
			if (!$groep->mag($this->action)) {
				$this->geentoegang();
			}
		}
		return parent::performAction($args);
	}

	/**
	 * Check permissions & valid params in actions.
	 * 
	 * @return boolean
	 */
	protected function mag($action, $method) {
		switch ($action) {
			case A::Beheren:
				return true;

			case 'overzicht':
			case A::Bekijken:
				return $method === 'GET';

			case 'overzicht':
			case GroepTab::Lijst:
			case GroepTab::Pasfotos:
			case GroepTab::Statistiek:
			case GroepTab::Emails:

			case A::Aanmaken:
			case A::Wijzigen:
			case A::Verwijderen:
			case A::Aanmelden:
			case A::Afmelden:
			case A::Bewerken:
				return $method === 'POST';

			default:
				return false;
		}
	}

	public function beheren() {
		if ($this->isPosted()) {
			$groepen = $this->model->find();
			$this->view = new DataTableResponse($groepen);
		} else {
			$body = new GroepenBeheerView($this->model);
			$this->view = new CsrLayoutPage($body);
			$this->view->addCompressedResources('datatable');
		}
	}

	public function overzicht() {
		$groepen = $this->model->find();
		$body = new GroepenView($this->model, $groepen);
		$this->view = new CsrLayoutPage($body);
	}

	public function bekijken(Groep $groep) {
		$body = new GroepView($groep, GroepTab::Lijst);
		$this->view = new CsrLayoutPage($body);
	}

	protected function groeptab(Groep $groep) {
		$this->view = new GroepView($groep, $this->action);
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
