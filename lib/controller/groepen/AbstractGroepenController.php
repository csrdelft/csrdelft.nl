<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\controller\framework\Controller;
use CsrDelft\model\AbstractGroepenModel;
use CsrDelft\model\ChangeLogModel;
use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\groepen\AbstractGroepLid;
use CsrDelft\model\entity\groepen\ActiviteitSoort;
use CsrDelft\model\entity\groepen\GroepKeuzeSelectie;
use CsrDelft\model\entity\groepen\GroepStatus;
use CsrDelft\model\entity\groepen\GroepTab;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\RemoveRowsResponse;
use CsrDelft\view\groepen\formulier\GroepAanmeldenForm;
use CsrDelft\view\groepen\formulier\GroepBewerkenForm;
use CsrDelft\view\groepen\formulier\GroepConverteerForm;
use CsrDelft\view\groepen\formulier\GroepForm;
use CsrDelft\view\groepen\formulier\GroepLidBeheerForm;
use CsrDelft\view\groepen\formulier\GroepLogboekForm;
use CsrDelft\view\groepen\formulier\GroepOpvolgingForm;
use CsrDelft\view\groepen\formulier\GroepPreviewForm;
use CsrDelft\view\groepen\GroepenBeheerData;
use CsrDelft\view\groepen\GroepenBeheerTable;
use CsrDelft\view\groepen\GroepenDeelnameGrafiek;
use CsrDelft\view\groepen\GroepenView;
use CsrDelft\view\groepen\GroepLogboekData;
use CsrDelft\view\groepen\GroepView;
use CsrDelft\view\groepen\leden\GroepEetwensView;
use CsrDelft\view\groepen\leden\GroepEmailsView;
use CsrDelft\view\groepen\leden\GroepLedenData;
use CsrDelft\view\groepen\leden\GroepLedenTable;
use CsrDelft\view\groepen\leden\GroepLijstView;
use CsrDelft\view\groepen\leden\GroepOmschrijvingView;
use CsrDelft\view\groepen\leden\GroepPasfotosView;
use CsrDelft\view\groepen\leden\GroepStatistiekView;
use CsrDelft\view\Icon;
use CsrDelft\view\JsonResponse;

/**
 * AbstractGroepenController.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @property AbstractGroepenModel $model
 */
abstract class AbstractGroepenController extends Controller {

	/** @var DataTable */
	protected $table;

	public function __construct($query, AbstractGroepenModel $model) {
		parent::__construct($query, $model);
	}

	public function performAction(array $args = []) {
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
					$this->exit_http(403);
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
					$this->exit_http(403);
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
				return $this->getMethod() == 'GET';

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
			case 'aanmelden2':
			case 'naar_ot':
			case 'bewerken':
			case 'afmelden':
				return $this->getMethod() == 'POST';

			default:
				return false;
		}
	}

	public function overzicht($soort = null) {
		if ($soort) {
			$groepen = $this->model->find('status = ? AND soort = ?', [GroepStatus::HT, $soort]);
		} else {
			$groepen = $this->model->find('status = ?', [GroepStatus::HT]);
		}
		$body = new GroepenView($this->model, $groepen, $soort); // controleert rechten bekijken per groep
		$this->view = view('default', ['content' => $body]);
	}

	public function bekijken(AbstractGroep $groep) {
		$groepen = $this->model->find('familie = ?', [$groep->familie]);
		if (property_exists($groep, 'soort')) {
			$soort = $groep->soort;
		} else {
			$soort = null;
		}
		$body = new GroepenView($this->model, $groepen, $soort, $groep->id); // controleert rechten bekijken per groep
		$this->view = view('default', ['content' => $body]);
	}

	public function deelnamegrafiek(AbstractGroep $groep) {
		$groepen = $this->model->find('familie = ?', [$groep->familie]);
		$this->view = new GroepenDeelnameGrafiek($groepen); // controleert GEEN rechten bekijken
	}

	public function omschrijving(AbstractGroep $groep) {
		if (!$groep->mag(AccessAction::Bekijken)) {
			$this->exit_http(403);
		}
		$this->view = new GroepOmschrijvingView($groep);
	}

	public function pasfotos(AbstractGroep $groep) {
		if (!$groep->mag(AccessAction::Bekijken)) {
			$this->exit_http(403);
		}
		$this->view = new GroepPasfotosView($groep);
	}

	public function lijst(AbstractGroep $groep) {
		if (!$groep->mag(AccessAction::Bekijken)) {
			$this->exit_http(403);
		}
		$this->view = new GroepLijstView($groep);
	}

	public function stats(AbstractGroep $groep) {
		if (!$groep->mag(AccessAction::Bekijken)) {
			$this->exit_http(403);
		}
		$this->view = new GroepStatistiekView($groep);
	}

	public function emails(AbstractGroep $groep) {
		if (!$groep->mag(AccessAction::Bekijken)) {
			$this->exit_http(403);
		}
		$this->view = new GroepEmailsView($groep);
	}

	public function eetwens(AbstractGroep $groep) {
		if (!$groep->mag(AccessAction::Bekijken)) {
			$this->exit_http(403);
		}
		$this->view = new GroepEetwensView($groep);
	}

	public function zoeken($zoekterm = null) {
		if (!$zoekterm && !$this->hasParam('q')) {
			$this->exit_http(403);
		}
		if (!$zoekterm) {
			$zoekterm = $this->getParam('q');
		}
		$zoekterm = '%' . $zoekterm . '%';
		$limit = 5;
		if ($this->hasParam('limit')) {
			$limit = (int)$this->getParam('limit');
		}
		$result = [];
		foreach ($this->model->find('familie LIKE ? AND (status = ? OR status = ?)', [$zoekterm, GroepStatus::HT, GroepStatus::FT], null, null, $limit) as $groep) {
			if (!isset($result[$groep->familie])) {
				$type = classNameZonderNamespace(get_class($groep));
				$result[$groep->familie] = [
					'url' => $groep->getUrl() . '#' . $groep->id,
					'label' => 'Groepen',
					'value' => $type . ': ' . $groep->familie,
					'icon' => Icon::getTag($type),
				];
			}
		}
		$this->view = new JsonResponse($result);
	}

	public function beheren($soort = null) {
		if ($this->getMethod() == 'POST') {
			if ($soort) {
				$groepen = $this->model->find('soort = ?', [$soort]);
			} else {
				$groepen = $this->model->find();
			}
			$this->view = new GroepenBeheerData($groepen); // controleert GEEN rechten bekijken
		} else {
			$table = new GroepenBeheerTable($this->model);
			$this->view = view('default', ['content' => $table]);
			$this->table = $table;
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
			/**
			 * @var \CsrDelft\model\entity\profiel\Profiel $profiel
			 */
			$profiel = LoginModel::getProfiel();
			if (property_exists($groep, 'rechten_aanmelden') AND empty($groep->rechten_aanmelden)) {
				switch ($groep->soort) {

					case ActiviteitSoort::Lichting:
						$groep->rechten_aanmelden = 'Lichting:' . $profiel->lidjaar;
						break;

					case ActiviteitSoort::Verticale:
						$groep->rechten_aanmelden = 'Verticale:' . $profiel->verticale;
						break;

					case ActiviteitSoort::Kring:
						$kring = $profiel->getKring();
						if ($kring) {
							$groep->rechten_aanmelden = 'Kring:' . $kring->verticale . '.' . $kring->kring_nummer;
						}
						break;

					case ActiviteitSoort::Ondervereniging:
						$groep->rechten_aanmelden = 'Lichting:' . $profiel->lidjaar;
						break;
				}
			}
		} // opvolger
		else {
			/** @var AbstractGroep $old */
			$old = $this->model->retrieveByUUID($selection[0]);
			if (!$old) {
				$this->exit_http(403);
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
		$form = new GroepForm($groep, $this->model->getUrl() . $this->action, AccessAction::Aanmaken); // checks rechten aanmaken
		if ($this->getMethod() == 'GET') {
			$this->beheren();
			$form->setDataTableId($this->table->getDataTableId());
			$this->view->getRenderer()->assign('modal', $form);
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
			$this->view->getRenderer()->assign('modal', $form);
		} else {
			$this->view = $form;
		}
	}

	public function wijzigen(AbstractGroep $groep = null) {
		if ($groep) {
			if (!$groep->mag(AccessAction::Wijzigen)) {
				$this->exit_http(403);
			}
			$form = new GroepForm($groep, $groep->getUrl() . $this->action, AccessAction::Wijzigen); // checks rechten wijzigen
			if ($this->getMethod() == 'GET') {
				$this->beheren();
				$this->table->filter = $groep->naam;
				$form->setDataTableId($this->table->getDataTableId());
				$this->view->getRenderer()->assign('modal', $form);
			} elseif ($form->validate()) {
				ChangeLogModel::instance()->logChanges($form->diff());
				$this->model->update($groep);
				$this->view = new GroepenBeheerData([$groep]);
			} else {
				$this->view = $form;
			}
		} // beheren
		else {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			if (empty($selection)) {
				$this->exit_http(403);
			}
			/** @var AbstractGroep $groep */
			$groep = $this->model->retrieveByUUID($selection[0]);
			if (!$groep OR !$groep->mag(AccessAction::Wijzigen)) {
				$this->exit_http(403);
			}
			$form = new GroepForm($groep, $groep->getUrl() . $this->action, AccessAction::Wijzigen); // checks rechten wijzigen
			if ($form->validate()) {
				ChangeLogModel::instance()->logChanges($form->diff());
				$this->model->update($groep);
				$this->view = new GroepenBeheerData([$groep]);
			} else {
				$this->view = $form;
			}
		}
	}

	public function verwijderen(array $selection) {
		$response = [];
		foreach ($selection as $UUID) {
			/** @var AbstractGroep $groep */
			$groep = $this->model->retrieveByUUID($UUID);
			if (!$groep OR !$groep->mag(AccessAction::Verwijderen)) {
				continue;
			}

			if (count($groep->getLeden()) !== 0) {
				// TODO: Laat gebruiker weten dat de groep niet is verwijderd omdat er nog leden in zitten.
				continue;
			}

			ChangeLogModel::instance()->log($groep, 'delete', print_r($groep, true), null);
			$this->model->delete($groep);
			$response[] = $groep;
		}
		$this->view = new RemoveRowsResponse($response);
	}

	public function opvolging(array $selection) {
		/** @var AbstractGroep $groep */
		$groep = $this->model->retrieveByUUID($selection[0]);
		$form = new GroepOpvolgingForm($groep, $this->model->getUrl() . $this->action);
		if ($form->validate()) {
			$values = $form->getValues();
			$response = [];
			foreach ($selection as $UUID) {
				$groep = $this->model->retrieveByUUID($UUID);
				if (!$groep OR !$groep->mag(AccessAction::Opvolging)) {
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
		/** @var AbstractGroep $groep */
		$groep = $this->model->retrieveByUUID($selection[0]);
		$form = new GroepConverteerForm($groep, $this->model);
		if ($form->validate()) {
			$values = $form->getValues();
			/** @var AbstractGroepenModel $model */
			$model = $values['model']::instance();
			$converteer = get_class($model) !== get_class($this->model);
			$response = [];
			foreach ($selection as $UUID) {
				$groep = $this->model->retrieveByUUID($UUID);
				if (!$groep OR !$groep->mag(AccessAction::Wijzigen)) {
					continue;
				}
				if ($converteer) {
					ChangeLogModel::instance()->log($groep, 'class', get_class($groep), $model::ORM);
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
		$response = [];
		foreach ($selection as $UUID) {
			/** @var AbstractGroep $groep */
			$groep = $this->model->retrieveByUUID($UUID);
			if (!$groep OR !property_exists($groep, 'aanmelden_tot') OR time() > strtotime($groep->aanmelden_tot) OR !$groep->mag(AccessAction::Wijzigen)) {
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
			$this->exit_http(403);
		}
		/** @var AbstractGroep $groep */
		$groep = $this->model->retrieveByUUID($selection[0]);
		if (!$groep OR !$groep->mag(AccessAction::Bekijken)) {
			$this->exit_http(403);
		}
		$this->view = new GroepPreviewForm($groep);
	}

	/**
	 * @param AbstractGroep|null $groep
	 * @throws CsrToegangException
	 */
	public function logboek(AbstractGroep $groep = null) {
		// data request
		if ($groep) {
			if (!$groep->mag(AccessAction::Bekijken)) {
				$this->exit_http(403);
			}
			$data = ChangeLogModel::instance()->find('subject = ?', [$groep->getUUID()]);
			$this->view = new GroepLogboekData($data);
		} // popup request
		else {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			/** @var AbstractGroep $groep */
			$groep = $this->model->retrieveByUUID($selection[0]);
			if (!$groep || !$groep->mag(AccessAction::Bekijken)) {
				throw new CsrToegangException('Kan logboek niet vinden', 403);
			}
			$this->view = new GroepLogboekForm($groep);
		}
	}

	public function leden(AbstractGroep $groep) {
		if (!$groep->mag(AccessAction::Bekijken)) {
			$this->exit_http(403);
		}
		if ($this->getMethod() == 'POST') {
			$this->view = new GroepLedenData($groep::getLedenModel()->getLedenVoorGroep($groep));
		} else {
			$this->view = new GroepLedenTable($groep::getLedenModel(), $groep);
		}
	}

	public function aanmelden2(AbstractGroep $groep, $uid) {
		$model = $groep::getLedenModel();

		if (!$groep->mag(AccessAction::Aanmelden)) {
			$this->exit_http(403);
		}
		$lid = $model->nieuw($groep, $uid);

		$opmerking = $this->getPost('opmerking2');

		$keuzes = [];
		foreach ($opmerking as $keuze) {
			$keuzes[] = new GroepKeuzeSelectie($keuze['naam'], $keuze['selectie']);
		}

		if (!$groep->valideerOpmerking($keuzes)) {
			$this->exit_http(400);
		}

		$lid->opmerking2 = $keuzes;

		ChangeLogModel::instance()->log($groep, 'aanmelden', null, $lid->uid);
		$model->create($lid);

		$this->view = new JsonResponse(['success' => true]);
	}

	public function aanmelden(AbstractGroep $groep, $uid = null) {
		$model = $groep::getLedenModel();
		if ($uid) {
			if (!$groep->mag(AccessAction::Aanmelden)) {
				$this->exit_http(403);
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
		} // beheren
		else {
			if (!$groep->mag(AccessAction::Beheren)) {
				$this->exit_http(403);
			}
			$lid = $model->nieuw($groep, null);
			$leden = group_by_distinct('uid', $groep->getLeden());
			$form = new GroepLidBeheerForm($lid, $groep->getUrl() . $this->action, array_keys($leden));
			if ($form->validate()) {
				ChangeLogModel::instance()->log($groep, 'aanmelden', null, $lid->uid);
				$model->create($lid);
				$this->view = new GroepLedenData([$lid]);
			} else {
				$this->view = $form;
			}
		}
	}

	public function bewerken(AbstractGroep $groep, $uid = null) {
		$model = $groep::getLedenModel();
		if ($uid) {
			if (!$groep->mag(AccessAction::Bewerken)) {
				$this->exit_http(403);
			}
			$lid = $model->get($groep, $uid);
			$form = new GroepBewerkenForm($lid, $groep);
			if ($form->validate()) {
				ChangeLogModel::instance()->logChanges($form->diff());
				$model->update($lid);
			}
			$this->view = $form;
		} // beheren
		else {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			if ($selection) {
				/** @var AbstractGroepLid $lid */
				$lid = $model->retrieveByUUID($selection[0]);
			} else {
				$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
				$uid = filter_input(INPUT_POST, 'uid', FILTER_SANITIZE_STRING);
				$lid = $model->get($id, $uid);
			}

			if (!$lid) {
				$this->exit_http(403);
			}
			if (!$groep->mag(AccessAction::Beheren)) {
				$this->exit_http(403);
			}
			$form = new GroepLidBeheerForm($lid, $groep->getUrl() . $this->action);
			if ($form->validate()) {
				ChangeLogModel::instance()->logChanges($form->diff());
				$model->update($lid);
				$this->view = new GroepLedenData([$lid]);
			} else {
				$this->view = $form;
			}
		}
	}

	public function afmelden(AbstractGroep $groep, $uid = null) {
		$model = $groep::getLedenModel();
		if ($uid) {
			if (!$groep->mag(AccessAction::Afmelden) AND !$groep->mag(AccessAction::Beheren)) { // A::Beheren voor afmelden via context-menu
				$this->exit_http(403);
			}
			$lid = $model->get($groep, $uid);
			ChangeLogModel::instance()->log($groep, 'afmelden', $lid->uid, null);
			$model->delete($lid);
			$this->view = new GroepView($groep);
		} // beheren
		else {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			if (empty($selection)) {
				$this->exit_http(403);
			}
			$response = [];
			foreach ($selection as $UUID) {
				$lid = $model->retrieveByUUID($UUID);
				if (!$groep->mag(AccessAction::Beheren)) {
					continue;
				}
				ChangeLogModel::instance()->log($groep, 'afmelden', $lid->uid, null);
				$model->delete($lid);
				$response[] = $lid;
			}
			$this->view = new RemoveRowsResponse($response);
		}
	}

	public function naar_ot(AbstractGroep $groep, $uid = null) {
		$model = $groep::getLedenModel();

		// Vind de groep uit deze familie met het laatste eind_moment
		$ot_groep_statement = $this->model->find("familie = ? and status = 'ot'", [$groep->familie], null, 'eind_moment DESC');

		if ($ot_groep_statement->rowCount() === 0) {
			throw new CsrGebruikerException('Geen o.t. groep gevonden');
		}

		/** @var AbstractGroep $ot_groep */
		$ot_groep = $ot_groep_statement->fetch();

		if ($uid) {
			if ($ot_groep->getLid($uid)) {
				throw new CsrGebruikerException('Lid al onderdeel van o.t. groep');
			}
			if (!$groep->mag(AccessAction::Afmelden) AND !$groep->mag(AccessAction::Beheren) AND !$ot_groep->mag(AccessAction::Aanmelden)) { // A::Beheren voor afmelden via context-menu
				throw new CsrGebruikerException();
			}
			Database::transaction(function () use ($groep, $ot_groep, $uid, $model) {
				$lid = $model->get($groep, $uid);
				ChangeLogModel::instance()->log($groep, 'afmelden', $lid->uid, null);
				ChangeLogModel::instance()->log($ot_groep, 'aanmelden', $lid->uid, null);
				$model->delete($lid);
				$lid->groep_id = $ot_groep->id;
				$model->create($lid);
				$lid->groep_id = $groep->id; // Terugspelen naar gebruiker dat dit lid is verwijderd
			});
			$this->view = new GroepView($groep);
		} else {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			if (empty($selection)) {
				throw new CsrGebruikerException();
			}

			$response = Database::transaction(function () use ($selection, $groep, $ot_groep, $model) {
				$response = [];
				foreach ($selection as $UUID) {
					if (!$groep->mag(AccessAction::Beheren)) {
						throw new CsrGebruikerException();
					}
					$lid = $model->retrieveByUUID($UUID);
					if ($ot_groep->getLid($lid->uid)) {
						throw new CsrGebruikerException('Lid al onderdeel van o.t. groep');
					}
					ChangeLogModel::instance()->log($groep, 'afmelden', $lid->uid, null);
					ChangeLogModel::instance()->log($ot_groep, 'aanmelden', $lid->uid, null);
					$model->delete($lid);
					$lid->groep_id = $ot_groep->id;
					$model->create($lid);
					$lid->groep_id = $groep->id;

					$response[] = $lid;
				}
			});
			$this->view = new RemoveRowsResponse($response);
		}

	}

}
