<?php

require_once 'view/GroepenView.class.php';

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
		if ($this->hasParam(3)) { // id or action
			$this->action = $this->getParam(3);
		} else {
			$this->action = 'overzicht'; // default
		}
		switch ($this->action) {
			case 'overzicht':
			case A::Beheren:
			case A::Aanmaken:
			case A::Wijzigen:
			case A::Verwijderen:
			case 'leden':

				$model = $this->model;
				$algemeen = AccessModel::get($model::orm, $this->action, null);
				if (!LoginModel::mag($algemeen) AND ! DEBUG) {
					$this->geentoegang();
				}
				break;

			default:
				$id = (int) $this->action; // id
				$groep = $this->model->get($id);
				if (!$groep) {
					$this->geentoegang();
				}
				$args[] = $groep;
				if ($this->hasParam(4)) { // action
					$this->action = $this->getParam(4);
					if ($this->hasParam(5)) { // uid
						$uid = $this->getParam(5);
						if (!ProfielModel::existsUid($uid)) {
							$this->geentoegang();
						}
						$args[] = $uid;
					}
				} else {
					$this->action = A::Bekijken; // default
				}
				if (!$groep->mag($this->action) AND ! DEBUG) {
					$this->geentoegang();
				}
		}
		return parent::performAction($args);
	}

	/**
	 * Check permissions & valid params in performAction.
	 * 
	 * @return boolean
	 */
	protected function mag($action, $method) {
		switch ($action) {
			case A::Beheren:
			case 'leden':
			case 'rechten':
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

	public function beheren() {
		if ($this->isPosted()) {
			$groepen = $this->model->find();
			$this->view = new GroepenBeheerData($groepen);
		} else {
			$body = new GroepenBeheerTable($this->model);
			$this->view = new CsrLayoutPage($body);
			$this->view->addCompressedResources('datatable');
		}
	}

	public function aanmaken() {
		$groep = $this->model->nieuw();
		$form = new GroepForm($groep, groepenUrl . $this->action);
		if ($form->validate()) {
			$this->model->create($groep);
			$this->view = new GroepenBeheerData(array($groep));
		} else {
			$this->view = $form;
		}
	}

	public function wijzigen() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		if (isset($selection[0])) {
			$groep = $this->model->getUUID($selection[0]);
		} else {
			$groep = false;
		}
		if (!$groep) {
			$this->geentoegang();
		}
		$form = new GroepForm($groep, groepenUrl . $this->action);
		if ($form->validate()) {
			$this->model->update($groep);
			$this->view = new GroepenBeheerData(array($groep));
		} else {
			$this->view = $form;
		}
	}

	public function verwijderen() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		$response = array();
		foreach ($selection as $UUID) {
			$groep = $this->model->getUUID($UUID);
			if (!$groep) {
				$this->geentoegang();
			}
			$this->model->delete($groep);
			$response[] = $groep;
		}
		$this->view = new RemoveRowsResponse($response);
	}

	public function leden(Groep $groep) {
		if ($this->isPosted()) {
			$this->view = new GroepLedenData($groep->getLeden());
		} else {
			$class = $groep::leden;
			$this->view = new GroepLedenTable($class::instance(), $groep);
		}
	}

	public function aanmelden(Groep $groep, $uid = null) {
		if ($uid) {
			$class = $groep::leden;
			$lid = $class::instance()->nieuw($groep, LoginModel::getUid());
			$form = new GroepAanmeldingForm($lid, $this->action);
			if ($form->validate()) {
				$class = $groep::leden;
				$class::instance()->create($lid);
			}
			$this->view = $form;
		}
		// beheren
		else {
			$class = $groep::leden;
			$lid = $class::instance()->nieuw($groep, $uid);
			$form = new GroepLidForm($lid, $this->action);
			if ($form->validate()) {
				$class = $groep::leden;
				$class::instance()->create($lid);
				$this->view = new GroepLedenData(array($lid));
			} else {
				$this->view = $form;
			}
		}
	}

	public function bewerken(Groep $groep, $uid = null) {
		//TODO
	}

	public function afmelden(Groep $groep, $uid = null) {
		if ($uid) {
			
		}
		// beheren
		else {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			$response = array();
			foreach ($selection as $UUID) {
				$groep = $this->model->getUUID($UUID);
				if (!$groep) {
					$this->geentoegang();
				}
				$this->model->delete($groep);
				$response[] = $groep;
			}
			$this->view = new RemoveRowsResponse($response);
		}
	}

}
