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

	public function __construct($query, GroepenModel $model = null) {
		parent::__construct($query, $model);
		if ($model === null) {
			$this->model = GroepenModel::instance();
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'overzicht'; // default
		if ($this->hasParam(3)) { // id or action
			$this->action = $this->getParam(3);
		}
		switch ($this->action) {

			// geen groep id vereist
			case 'overzicht':
			case A::Beheren:
			case A::Aanmaken:
			case A::Wijzigen:
			case A::Verwijderen:
				break;

			// groep id vereist
			default:
				// param 3 bevat id
				$id = (int) $this->action; // id
				$groep = $this->model->get($id);
				if (!$groep) {
					$this->geentoegang();
				}
				$args['groep'] = $groep;

				// actie opgegeven?
				$this->action = A::Bekijken; // default
				if ($this->hasParam(4)) { // action
					$this->action = $this->getParam(4);

					// lidnummer opgegeven?
					if ($this->hasParam(5)) { // uid
						$uid = $this->getParam(5);
						$args['uid'] = $uid;
					}
				}
		}
		return parent::performAction($args);
	}

	/**
	 * Check permissions & valid params in performAction.
	 * 
	 * @return boolean
	 */
	protected function mag($action, array $args) {



		switch ($action) {
			case A::Rechten:
			case A::Beheren:
			case 'leden':
				return true;

			case 'overzicht':
			case A::Bekijken:
				return !$this->isPosted();

			case 'overzicht':
			case GroepTab::Pasfotos:
			case GroepTab::Lijst:
			case GroepTab::Statistiek:
			case GroepTab::Emails:
			case A::Aanmaken:
			case A::Wijzigen:
			case A::Verwijderen:
			case A::Aanmelden:
			case A::Afmelden:
			case A::Bewerken:
				return $this->isPosted();

			default:
				return false;
		}
	}

	public function overzicht($soort = null) {
		if ($soort) {
			$groepen = $this->model->find('soort = ?', array($soort));
		} else {
			$groepen = $this->model->find();
		}
		$body = new GroepenView($this->model, $groepen);
		$this->view = new CsrLayoutPage($body);
	}

	public function bekijken(Groep $groep) {
		$body = new GroepView($groep, GroepTab::Pasfotos);
		$this->view = new CsrLayoutPage($body);
	}

	public function pasfotos(Groep $groep) {
		$this->view = new GroepPasfotosView($groep);
	}

	public function lijst(Groep $groep) {
		$this->view = new GroepLijstView($groep);
	}

	public function stats(Groep $groep) {
		$this->view = new GroepStatistiekView($groep);
	}

	public function emails(Groep $groep) {
		$this->view = new GroepEmailsView($groep);
	}

	public function beheren($soort = null) {
		if ($this->isPosted()) {
			if ($soort) {
				$groepen = $this->model->find('soort = ?', array($soort));
			} else {
				$groepen = $this->model->find();
			}
			$this->view = new GroepenBeheerData($groepen);
		} else {
			$body = new GroepenBeheerTable($this->model);
			$this->view = new CsrLayoutPage($body);
			$this->view->addCompressedResources('datatable');
		}
	}

	public function aanmaken($soort = null) {
		$groep = $this->model->nieuw($soort);
		$form = new GroepForm($groep, groepenUrl . $this->action);
		if ($form->validate()) {
			$this->model->create($groep);
			$this->view = new GroepenBeheerData(array($groep));
		} else {
			$this->view = $form;
		}
	}

	public function wijzigen($soort = null) {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		if (isset($selection[0])) {
			$groep = $this->model->getUUID($selection[0]);
		} else {
			$groep = false;
		}
		if (!$groep OR ! $groep->mag($this->action)) {
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

	public function verwijderen($soort = null) {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		$response = array();
		foreach ($selection as $UUID) {
			$groep = $this->model->getUUID($UUID);
			if (!$groep OR ! $groep->mag($this->action)) {
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
			$leden = $groep::leden;
			$this->view = new GroepLedenTable($leden::instance(), $groep);
		}
	}

	public function aanmelden(Groep $groep, $uid = null) {
		$leden = $groep::leden;
		$model = $leden::instance();
		if ($uid) {
			$lid = $model->nieuw($groep, LoginModel::getUid());
			$form = new GroepAanmeldenForm($lid, $groep->getSuggesties(), $groep->keuzelijst);
			if ($form->validate()) {
				$model->create($lid);
				$this->view = new GroepView($groep);
			} else {
				$this->view = $form;
			}
		}
		// beheren
		else {
			$lid = $model->nieuw($groep, $uid);
			$uids = array_keys(group_by_distinct('uid', $groep->getLeden()));
			$form = new GroepLidBeheerForm($lid, $this->action, $uids);
			if ($form->validate()) {
				$model->create($lid);
				$this->view = new GroepLedenData(array($lid));
			} else {
				$this->view = $form;
			}
		}
	}

	public function bewerken(Groep $groep, $uid = null) {
		$leden = $groep::leden;
		$model = $leden::instance();
		if ($uid) {
			$lid = $model->get($groep, $uid);
			$form = new GroepBewerkenForm($lid, $groep->getSuggesties(), $groep->keuzelijst);
			if ($form->validate()) {
				$model->update($lid);
			}
			$this->view = $form;
		}
		// beheren
		else {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			if (!isset($selection[0])) {
				$this->geentoegang();
			}
			$lid = $model->getUUID($selection[0]);
			$form = new GroepLidBeheerForm($lid, $this->action);
			if ($form->validate()) {
				$model->update($lid);
				$this->view = new GroepLedenData(array($lid));
			} else {
				$this->view = $form;
			}
		}
	}

	public function afmelden(Groep $groep, $uid = null) {
		$leden = $groep::leden;
		$model = $leden::instance();
		if ($uid) {
			$lid = $model->get($groep, $uid);
			$lid->status = GroepStatus::OT;
			$model->delete($lid);
			//TODO: $this->view = 
		}
		// beheren
		else {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			$response = array();
			foreach ($selection as $UUID) {
				$lid = $model->getUUID($UUID);
				$model->delete($lid);
				$response[] = $lid;
			}
			$this->view = new RemoveRowsResponse($response);
		}
	}

	public function rechten(Groep $groep, $action = null) {
		$model = AccessModel::instance();
		switch ($action) {

			case A::Aanmaken:
				$ac = $model->nieuw(get_class($groep), $groep->id);
				$form = new GroepRechtenForm($ac, $groep, $action, $this->model);
				if ($form->validate()) {
					$model->create($ac);
					$this->view = new GroepRechtenData(array($ac));
				} else {
					$this->view = $form;
				}
				return;

			case A::Wijzigen:
				$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
				if (!isset($selection[0])) {
					$this->geentoegang();
				}
				$ac = $model->getUUID($selection[0]);
				if ($ac->resource === '*') {
					// recursive permissions
					$admin = $model->get(get_class($groep), A::Rechten, '*');
					if (!$admin OR ! LoginModel::mag($admin)) {
						$this->geentoegang();
					}
				}
				$form = new GroepRechtenForm($ac, $groep, $action, $this->model);
				if ($form->validate()) {
					$model->update($ac);
					$this->view = new GroepRechtenData(array($ac));
				} else {
					$this->view = $form;
				}
				return;

			case A::Verwijderen:
				$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
				$response = array();
				foreach ($selection as $UUID) {
					$ac = $model->getUUID($UUID);
					$model->delete($ac);
					$response[] = $ac;
				}
				$this->view = new RemoveRowsResponse($response);
				return;

			default:
				if ($this->isPosted()) {
					$acl = $model->find('environment = ? AND (resource = ? OR resource = ?)', array(get_class($groep), '*', $groep->id));
					$this->view = new GroepRechtenData($acl);
				} else {
					$this->view = new GroepRechtenTable($model, $groep);
				}
				return;
		}
	}

}
