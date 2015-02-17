<?php

require_once 'model/ChangeLogModel.class.php';
require_once 'view/GroepenView.class.php';

/**
 * GroepenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor groepen.
 */
class AbstractGroepenController extends Controller {

	public function __construct($query, AbstractGroepenModel $model) {
		parent::__construct($query, $model);
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
			case 'voorbeeld':
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
			case 'logboek':
				/**
				 * In case url param 3
				 * contains the ID this
				 * switch case is skipped.
				 */
				break;

			// Groep id vereist
			default:
				// Groep id in param 3?
				$id = $this->action;
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
			case 'voorbeeld':
			case 'opvolging':
			case 'converteren':
			case 'sluiten':
			case 'logboek':
			case 'omschrijving':
			case 'deelnamegrafiek':
			case GroepTab::Pasfotos:
			case GroepTab::Lijst:
			case GroepTab::Statistiek:
			case GroepTab::Emails:
			case GroepTab::Eetwens:
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

	public function bekijken(AbstractGroep $groep) {
		$groepen = $this->model->find('familie = ?', array($groep->familie));
		if (property_exists($groep, 'soort')) {
			$soort = $groep->soort;
		} else {
			$soort = null;
		}
		$body = new GroepenView($this->model, $groepen, $soort, $groep->id); // controleert rechten bekijken per groep
		$this->view = new CsrLayoutPage($body);
	}

	public function deelnamegrafiek(AbstractGroep $groep) {
		$groepen = $this->model->find('familie = ?', array($groep->familie));
		$this->view = new GroepenDeelnameGrafiek($groepen); // controleert GEEN rechten bekijken
	}

	public function omschrijving(AbstractGroep $groep) {
		if (!$groep->mag(A::Bekijken)) {
			$this->geentoegang();
		}
		$this->view = new GroepOmschrijvingView($groep);
	}

	public function pasfotos(AbstractGroep $groep) {
		if (!$groep->mag(A::Bekijken)) {
			$this->geentoegang();
		}
		$this->view = new GroepPasfotosView($groep);
	}

	public function lijst(AbstractGroep $groep) {
		if (!$groep->mag(A::Bekijken)) {
			$this->geentoegang();
		}
		$this->view = new GroepLijstView($groep);
	}

	public function stats(AbstractGroep $groep) {
		if (!$groep->mag(A::Bekijken)) {
			$this->geentoegang();
		}
		$this->view = new GroepStatistiekView($groep);
	}

	public function emails(AbstractGroep $groep) {
		if (!$groep->mag(A::Bekijken)) {
			$this->geentoegang();
		}
		$this->view = new GroepEmailsView($groep);
	}

	public function eetwens(AbstractGroep $groep) {
		if (!$groep->mag(A::Bekijken)) {
			$this->geentoegang();
		}
		$this->view = new GroepEetwensView($groep);
	}

	public function zoeken() {
		if (!$this->hasParam('q')) {
			$this->geentoegang();
		}
		$zoekterm = '%' . $this->getParam('q') . '%';
		$limit = 5;
		if ($this->hasParam('limit')) {
			$limit = (int) $this->getParam('limit');
		}
		$result = array();
		foreach ($this->model->find('familie LIKE ?', array($zoekterm), null, null, $limit) as $groep) {
			if (!isset($result[$groep->familie])) {
				$result[$groep->familie] = array(
					'url'	 => $groep->getUrl() . '#' . $groep->id,
					'label'	 => 'Groepen',
					'value'	 => get_class($groep) . ':' . $groep->familie
				);
			}
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
			if (property_exists($groep, 'rechten_aanmelden') AND empty($groep->rechten_aanmelden)) {
				switch ($groep->soort) {

					case ActiviteitSoort::Lichting:
						$groep->rechten_aanmelden = 'Lichting:' . LoginModel::getProfiel()->lidjaar;
						break;

					case ActiviteitSoort::Verticale:
						$groep->rechten_aanmelden = 'Verticale:' . LoginModel::getProfiel()->verticale;
						break;

					case ActiviteitSoort::Kring:
						$kring = LoginModel::getProfiel()->getKring();
						if ($kring) {
							$groep->rechten_aanmelden = 'Kring:' . $kring->verticale . '.' . $kring->kring_nummer;
						}
						break;

					case ActiviteitSoort::Huis:
						$woonoord = LoginModel::getProfiel()->getWoonoord();
						if ($woonoord) {
							$groep->rechten_aanmelden = 'Woonoord:' . $woonoord->familie;
						}
						break;

					case ActiviteitSoort::Ondervereniging:
						$groep->rechten_aanmelden = 'Lichting:' . LoginModel::getProfiel()->lidjaar;
						break;
				}
			}
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
			$form->setDataTableId($this->view->getBody()->getDataTableId());
			$this->view->modal = $form;
			return;
		} elseif ($form->validate()) {
			ChangeLogModel::instance()->log($groep, 'create', null, print_r($groep, true));
			$this->model->create($groep);
			$response[] = $groep;
			if ($old) {
				$old->status = GroepStatus::OT;
				$this->model->update($old);
				$response[] = $old;
			}
			$this->view = new GroepenBeheerData($response);
			setMelding(get_class($groep) . ' succesvol aangemaakt!', 1);
			$form = new GroepPreviewForm($groep);
			$this->view->modal = $form->getHtml();
		} else {
			$this->view = $form;
		}
	}

	public function wijzigen(AbstractGroep $groep = null) {
		if ($groep) {
			if (!$groep->mag(A::Wijzigen)) {
				$this->geentoegang();
			}
			$form = new GroepForm($groep, $groep->getUrl() . $this->action); // checks rechten aanmaken
			if (!$this->isPosted()) {
				$this->beheren();
				$this->view->getBody()->filter = $groep->naam;
				$form->setDataTableId($this->view->getBody()->getDataTableId());
				$this->view->modal = $form;
			} elseif ($form->validate()) {
				ChangeLogModel::instance()->logChanges($form->diff());
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
				ChangeLogModel::instance()->logChanges($form->diff());
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
			ChangeLogModel::instance()->log($groep, 'delete', print_r($groep, true), null);
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
				ChangeLogModel::instance()->log($groep, 'familie', $groep->familie, $values['familie']);
				ChangeLogModel::instance()->log($groep, 'status', $groep->status, $values['status']);
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
					ChangeLogModel::instance()->log($groep, 'class', get_class($groep), $model::orm);
					$nieuw = $model->converteer($groep, $this->model, $values['soort']);
					if ($nieuw) {
						$response[] = $groep;
					}
				} elseif (property_exists($groep, 'soort')) {
					ChangeLogModel::instance()->log($groep, 'soort', $groep->soort, $values['soort']);
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
			ChangeLogModel::instance()->log($groep, 'aanmelden_tot', $groep->aanmelden_tot, getDateTime());
			$groep->aanmelden_tot = getDateTime();
			$this->model->update($groep);
			$response[] = $groep;
		}
		$this->view = new GroepenBeheerData($response);
	}

	public function voorbeeld(array $selection) {
		if (empty($selection)) {
			$this->geentoegang();
		}
		$groep = $this->model->getUUID($selection[0]);
		if (!$groep OR ! $groep->mag(A::Bekijken)) {
			$this->geentoegang();
		}
		$this->view = new GroepPreviewForm($groep);
	}

	public function logboek(AbstractGroep $groep = null) {
		// data request
		if ($groep) {
			if (!$groep->mag(A::Bekijken)) {
				$this->geentoegang();
			}
			$data = ChangeLogModel::instance()->find('subject = ?', array($groep->getUUID()));
			$this->view = new GroepLogboekData($data);
		}
		// popup request
		else {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			$groep = $this->model->getUUID($selection[0]);
			if (!$groep->mag(A::Bekijken)) {
				$this->geentoegang();
			}
			$this->view = new GroepLogboekForm($groep);
		}
	}

	public function leden(AbstractGroep $groep) {
		if (!$groep->mag(A::Bekijken)) {
			$this->geentoegang();
		}
		$leden = $groep::leden;
		if ($this->isPosted()) {
			$this->view = new GroepLedenData($leden::instance()->getLedenVoorGroep($groep));
		} else {
			$this->view = new GroepLedenTable($leden::instance(), $groep);
		}
	}

	public function aanmelden(AbstractGroep $groep, $uid = null) {
		$leden = $groep::leden;
		$model = $leden::instance();
		if ($uid) {
			if (!$groep->mag(A::Aanmelden)) {
				$this->geentoegang();
			}
			$lid = $model->nieuw($groep, $uid);
			$form = new GroepAanmeldenForm($lid, $groep);
			if ($form->validate()) {
				ChangeLogModel::instance()->log($groep, 'aanmelden', null, $lid->uid);
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
			$leden = group_by_distinct('uid', $groep->getLeden());
			$form = new GroepLidBeheerForm($lid, $groep->getUrl() . $this->action, array_keys($leden));
			if ($form->validate()) {
				ChangeLogModel::instance()->log($groep, 'aanmelden', null, $lid->uid);
				$model->create($lid);
				$this->view = new GroepLedenData(array($lid));
			} else {
				$this->view = $form;
			}
		}
	}

	public function bewerken(AbstractGroep $groep, $uid = null) {
		$leden = $groep::leden;
		$model = $leden::instance();
		if ($uid) {
			if (!$groep->mag(A::Bewerken)) {
				$this->geentoegang();
			}
			$lid = $model->get($groep, $uid);
			$form = new GroepBewerkenForm($lid, $groep);
			if ($form->validate()) {
				ChangeLogModel::instance()->logChanges($form->diff());
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
				ChangeLogModel::instance()->logChanges($form->diff());
				$model->update($lid);
				$this->view = new GroepLedenData(array($lid));
			} else {
				$this->view = $form;
			}
		}
	}

	public function afmelden(AbstractGroep $groep, $uid = null) {
		$leden = $groep::leden;
		$model = $leden::instance();
		if ($uid) {
			if (!$groep->mag(A::Afmelden)) {
				$this->geentoegang();
			}
			$lid = $model->get($groep, $uid);
			ChangeLogModel::instance()->log($groep, 'afmelden', $lid->uid, null);
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
				ChangeLogModel::instance()->log($groep, 'afmelden', $lid->uid, null);
				$model->delete($lid);
				$response[] = $lid;
			}
			$this->view = new RemoveRowsResponse($response);
		}
	}

}
