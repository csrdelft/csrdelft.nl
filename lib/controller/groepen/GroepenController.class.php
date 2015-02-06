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
			case 'zoeken':
			case 'nieuw':
			case 'aanmaken':
				// Soort in param 4?
				if ($this->hasParam(4)) {
					$args['soort'] = $this->getParam(4);
				}
				break;

			// Groep id of selectie vereist
			case 'wijzigen':
				/**
				 * In case of GET the url param 3
				 * contains the ID and this
				 * switch case is skipped.
				 */
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
			case 'nieuw':
			case 'aanmaken':
				return true;

			case 'overzicht':
			case 'bekijken':
			case 'zoeken':
				return !$this->isPosted();

			case 'overzicht':
			case 'opvolging':
			case 'converteren':
			case 'sluiten':
			case 'omschrijving':
			case 'deelnamegrafiek':
			case GroepTab::Pasfotos:
			case GroepTab::Lijst:
			case GroepTab::Statistiek:
			case GroepTab::Emails:
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
		$body = new GroepenView($this->model, $groepen, $soort); // controleert rechten bekijken per groep
		$this->view = new CsrLayoutPage($body);
	}

	public function bekijken(Groep $groep) {
		$groepen = $this->model->find('familie = ?', array($groep->familie));
		if (property_exists($groep, 'soort')) {
			$soort = $groep->soort;
		} else {
			$soort = null;
		}
		$body = new GroepenView($this->model, $groepen, $soort, $groep->id); // controleert rechten bekijken per groep
		$this->view = new CsrLayoutPage($body);
	}

	public function deelnamegrafiek(Groep $groep) {
		$groepen = $this->model->find('familie = ?', array($groep->familie));
		$this->view = new GroepenDeelnameGrafiek($groepen); // controleert GEEN rechten bekijken
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

	public function zoeken() {
		if (!$this->hasParam('q')) {
			$this->geentoegang();
		}
		$zoekterm = $this->getParam('q');
		$limit = 5;
		if ($this->hasParam('limit')) {
			$limit = (int) $this->getParam('limit');
		}
		$result = array();
		foreach ($this->model->find('familie = ?', array($zoekterm), null, null, $limit) as $groep) {
			$result[$groep->familie] = array(
				'value' => get_class($groep) . ':' . $groep->familie
			);
		}
		$this->view = new JsonResponse($result);
	}

	public function beheren($soort = null) {
		if ($this->isPosted()) {
			if ($soort) {
				$groepen = $this->model->find('soort = ?', array($soort));
			} else {
				$groepen = $this->model->find();
			}
			$this->view = new GroepenBeheerData($groepen); // controleert GEEN rechten bekijken
		} else {
			$table = new GroepenBeheerTable($this->model);
			$this->view = new CsrLayoutPage($table);
			$this->view->addCompressedResources('datatable');
		}
	}

	public function nieuw($soort = null) {
		return $this->aanmaken($soort);
	}

	public function aanmaken($soort = null) {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		if (empty($selection)) {
			$old = null;
			$groep = $this->model->nieuw($soort);
		}
		// opvolger
		else {
			$old = $this->model->getUUID($selection[0]);
			if (!$old) {
				$this->geentoegang();
			}
			if (property_exists($old, 'soort')) {
				$soort = $old->soort;
			}
			$groep = $this->model->nieuw($soort);
			$groep->naam = $old->naam;
			$groep->familie = $old->familie;
			$groep->samenvatting = $old->samenvatting;
			$groep->omschrijving = $old->omschrijving;
			if (property_exists($old, 'rechten_aanmelden')) {
				$groep->rechten_aanmelden = $old->rechten_aanmelden;
			}
		}
		$form = new GroepForm($groep, $this->model->getUrl() . $this->action); // checks rechten aanmaken
		if (!$this->isPosted()) {
			$this->beheren();
			$form->tableId = $this->view->getBody()->getTableId();
			$this->view->modal = $form;
			return;
		} elseif ($form->validate()) {
			$this->model->create($groep);
			$response[] = $groep;
			if ($old) {
				$old->status = GroepStatus::OT;
				$this->model->update($old);
				$response[] = $old;
			}
			$this->view = new GroepenBeheerData($response);
		} else {
			$this->view = $form;
		}
	}

	public function wijzigen(Groep $groep = null) {
		if ($groep) {
			if (!$groep->mag(A::Wijzigen)) {
				$this->geentoegang();
			}
			$form = new GroepForm($groep, $groep->getUrl() . $this->action); // checks rechten aanmaken
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
			$form = new GroepForm($groep, $this->model->getUrl() . $this->action); // checks rechten aanmaken
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
			$values = $form->getValues();
			$model = $values['model']::instance();
			$converteer = get_class($model) !== get_class($this->model);
			$response = array();
			foreach ($selection as $UUID) {
				$groep = $this->model->getUUID($UUID);
				if (!$groep OR ! $groep->mag(A::Wijzigen)) {
					continue;
				}
				if ($converteer) {
					$nieuw = $model->converteer($groep, $this->model, $values['soort']);
					if ($nieuw) {
						$response[] = $groep;
					}
				} elseif (property_exists($groep, 'soort')) {
					$groep->soort = $values['soort'];
					$rowCount = $this->model->update($groep);
					if ($rowCount > 0) {
						$response[] = $groep;
					}
				}
			}
			if ($converteer) {
				$this->view = new RemoveRowsResponse($response);
			} else {
				$this->view = new GroepenBeheerData($response);
			}
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
			$form = new GroepAanmeldenForm($lid, $groep);
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
			$form = new GroepBewerkenForm($lid, $groep);
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
			if (empty($selection)) {
				$this->geentoegang();
			}
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
