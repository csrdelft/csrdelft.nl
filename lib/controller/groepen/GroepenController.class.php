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

			// Selectie vereist
			case 'verwijderen':
			case 'opvolging':
			case 'converteren':
			case 'sluiten':
				$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
				if (empty($selection)) {
					$this->geentoegang();
				} else {
					$args['selection'] = $selection;
				}
				break;

			// Geen argumenten vereist
			case 'overzicht':
			case 'beheren':
			case 'aanmaken':
				// Soort in param 4?
				if ($this->hasParam(4)) {
					$args['soort'] = $this->getParam(4);
				}
				break;

			// Groep id of selectie vereist
			case 'wijzigen':
				break;

			// Groep id vereist
			default:
				// Groep id in param 3?
				$id = (int) $this->action;
				$groep = $this->model->get($id);
				if (!$groep) {
					$this->geentoegang();
				}
				$args['groep'] = $groep;
				$this->action = 'bekijken'; // default
				$uid = null;

				// Actie in param 4?
				if ($this->hasParam(4)) {
					$this->action = $this->getParam(4);

					// Lidnummer in param 5?
					if ($this->hasParam(5)) {
						$uid = $this->getParam(5);
						$args['uid'] = $uid;
					}
				}
		}
		return parent::performAction($args);
	}

	/**
	 * Check permissions & valid params in actions.
	 * 
	 * @param string $action
	 * @param array $args
	 * @return boolean
	 */
	protected function mag($action, array $args) {
		switch ($action) {

			case 'leden':
			case 'beheren':
			case 'wijzigen':
				return true;

			case 'overzicht':
			case 'bekijken':
				return !$this->isPosted();

			case 'overzicht':
			case 'opvolging':
			case 'converteren':
			case 'sluiten':
			case 'omschrijving':
			case GroepTab::Pasfotos:
			case GroepTab::Lijst:
			case GroepTab::Statistiek:
			case GroepTab::Emails:
			case 'aanmaken':
			case 'verwijderen':
			case 'aanmelden':
			case 'bewerken':
			case 'afmelden':
				return $this->isPosted();

			default:
				return false;
		}
	}

	public function overzicht($soort = null) {
		if ($soort) {
			$groepen = $this->model->find('status = ? AND soort = ?', array(GroepStatus::HT, $soort));
		} else {
			$groepen = $this->model->find('status = ?', array(GroepStatus::HT));
		}
		$body = new GroepenView($this->model, $groepen); // checked rechten bekijken per groep
		$this->view = new CsrLayoutPage($body);
	}

	public function bekijken(Groep $groep) {
		$groepen = $this->model->find('familie = ?', array($groep->familie));
		$body = new GroepenView($this->model, $groepen); // checked rechten bekijken per groep
		$this->view = new CsrLayoutPage($body);
	}

	public function omschrijving(Groep $groep) {
		if (!$groep->mag(A::Bekijken)) {
			$this->geentoegang();
		}
		$this->view = new GroepOmschrijvingView($groep);
	}

	public function pasfotos(Groep $groep) {
		if (!$groep->mag(A::Bekijken)) {
			$this->geentoegang();
		}
		$this->view = new GroepPasfotosView($groep);
	}

	public function lijst(Groep $groep) {
		if (!$groep->mag(A::Bekijken)) {
			$this->geentoegang();
		}
		$this->view = new GroepLijstView($groep);
	}

	public function stats(Groep $groep) {
		if (!$groep->mag(A::Bekijken)) {
			$this->geentoegang();
		}
		$this->view = new GroepStatistiekView($groep);
	}

	public function emails(Groep $groep) {
		if (!$groep->mag(A::Bekijken)) {
			$this->geentoegang();
		}
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
			$table = new GroepenBeheerTable($this->model);
			$this->view = new CsrLayoutPage($table);
			$this->view->addCompressedResources('datatable');
		}
	}

	public function aanmaken($soort = null) {
		$groep = $this->model->nieuw($soort);
		$form = new GroepForm($groep, $this->model->getUrl() . $this->action);
		// get posted value
		if (property_exists($groep, 'soort')) {
			$soort = $groep->soort;
		}
		if (!$groep::magAlgemeen(A::Aanmaken, $soort)) {
			setMelding('U mag dit soort ' . $this->model->getNaam() . ' niet aanmaken', -1);
		} elseif ($form->validate()) {
			$this->model->create($groep);
			$this->view = new GroepenBeheerData(array($groep));
			return;
		}
		$this->view = $form;
	}

	public function wijzigen(Groep $groep = null) {
		if ($groep) {
			if (!$groep->mag(A::Wijzigen)) {
				$this->geentoegang();
			}
			$form = new GroepForm($groep, $groep->getUrl() . $this->action);
			if (!$this->isPosted()) {
				$this->beheren();
				$this->view->getBody()->filter = $groep->naam;
				$form->tableId = $this->view->getBody()->getTableId();
				$this->view->modal = $form;
			} elseif ($form->validate()) {
				$this->model->update($groep);
				$this->view = new GroepenBeheerData(array($groep));
			} else {
				$this->view = $form;
			}
		}
		// beheren
		else {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			if (empty($selection)) {
				$this->geentoegang();
			}
			$groep = $this->model->getUUID($selection[0]);
			if (!$groep OR ! $groep->mag(A::Wijzigen)) {
				$this->geentoegang();
			}
			$form = new GroepForm($groep, $this->model->getUrl() . $this->action);
			if ($form->validate()) {
				$this->model->update($groep);
				$this->view = new GroepenBeheerData(array($groep));
			} else {
				$this->view = $form;
			}
		}
	}

	public function verwijderen(array $selection) {
		$response = array();
		foreach ($selection as $UUID) {
			$groep = $this->model->getUUID($UUID);
			if (!$groep OR ! $groep->mag(A::Verwijderen)) {
				continue;
			}
			$this->model->delete($groep);
			$response[] = $groep;
		}
		$this->view = new RemoveRowsResponse($response);
	}

	public function opvolging(array $selection) {
		$groep = $this->model->getUUID($selection[0]);
		$form = new GroepOpvolgingForm($groep, $this->model->getUrl() . $this->action);
		if ($form->validate()) {
			$values = $form->getValues();
			$response = array();
			foreach ($selection as $UUID) {
				$groep = $this->model->getUUID($UUID);
				if (!$groep OR ! $groep->mag(A::Opvolging)) {
					continue;
				}
				$groep->familie = $values['familie'];
				$groep->status = $values['status'];
				$this->model->update($groep);
				$response[] = $groep;
			}
			$this->view = new GroepenBeheerData($response);
		} else {
			$this->view = $form;
		}
	}

	public function converteren(array $selection) {
		$groep = $this->model->getUUID($selection[0]);
		$form = new GroepConverteerForm($groep, $this->model);
		if ($form->validate()) {
			$model = $form->findByName('class')->getValue();
			if ($model === get_class($this->model)) {
				setMelding('Geen wijziging', 0);
				$this->view = $form;
				return;
			}
			// get posted value
			$orm = $model::orm;
			if (!$orm::magAlgemeen(A::Aanmaken)) {
				$this->geentoegang();
			}
			$response = array();
			foreach ($selection as $UUID) {
				$groep = $this->model->getUUID($UUID);
				if (!$groep OR ! $groep->mag(A::Wijzigen)) {
					continue;
				}
				$nieuw = $model::instance()->converteer($groep, $this->model);
				if ($nieuw) {
					$response[] = $groep;
				}
			}
			$this->view = new RemoveRowsResponse($response);
		} else {
			$this->view = $form;
		}
	}

	public function sluiten(array $selection) {
		$groep = $this->model->getUUID($selection[0]);
		$response = array();
		foreach ($selection as $UUID) {
			$groep = $this->model->getUUID($UUID);
			if (!$groep OR ! property_exists($groep, 'aanmelden_tot') OR time() > strtotime($groep->aanmelden_tot) OR ! $groep->mag(A::Wijzigen)) {
				continue;
			}
			$groep->aanmelden_tot = getDateTime();
			$this->model->update($groep);
			$response[] = $groep;
		}
		$this->view = new GroepenBeheerData($response);
	}

	public function leden(Groep $groep) {
		if (!$groep->mag(A::Bekijken)) {
			$this->geentoegang();
		}
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
			if (!$groep->mag(A::Aanmelden)) {
				$this->geentoegang();
			}
			$lid = $model->nieuw($groep, $uid);
			$form = new GroepAanmeldenForm($lid, $groep, $groep->getOpmerkingSuggesties(), $groep->keuzelijst);
			if ($form->validate()) {
				$model->create($lid);
				$this->view = new GroepPasfotosView($groep);
			} else {
				$this->view = $form;
			}
		}
		// beheren
		else {
			if (!$groep->mag(A::Beheren)) {
				$this->geentoegang();
			}
			$lid = $model->nieuw($groep, null);
			$uids = array_keys(group_by_distinct('uid', $groep->getLeden()));
			$form = new GroepLidBeheerForm($lid, $groep->getUrl() . $this->action, $uids);
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
			if (!$groep->mag(A::Bewerken)) {
				$this->geentoegang();
			}
			$lid = $model->get($groep, $uid);
			$form = new GroepBewerkenForm($lid, $groep, $groep->getOpmerkingSuggesties(), $groep->keuzelijst);
			if ($form->validate()) {
				$model->update($lid);
			}
			$this->view = $form;
		}
		// beheren
		else {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			if (empty($selection)) {
				$this->geentoegang();
			}
			$lid = $model->getUUID($selection[0]);
			if (!$groep->mag(A::Beheren)) {
				$this->geentoegang();
			}
			$form = new GroepLidBeheerForm($lid, $groep->getUrl() . $this->action);
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
			if (!$groep->mag(A::Afmelden)) {
				$this->geentoegang();
			}
			$lid = $model->get($groep, $uid);
			$model->delete($lid);
			$this->view = new GroepView($groep);
		}
		// beheren
		else {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			$response = array();
			foreach ($selection as $UUID) {
				$lid = $model->getUUID($UUID);
				if (!$groep->mag(A::Beheren)) {
					continue;
				}
				$model->delete($lid);
				$response[] = $lid;
			}
			$this->view = new RemoveRowsResponse($response);
		}
	}

}
