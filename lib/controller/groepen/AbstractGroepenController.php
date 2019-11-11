<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\controller\framework\QueryParamTrait;
use CsrDelft\model\AbstractGroepenModel;
use CsrDelft\model\ChangeLogModel;
use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\groepen\AbstractGroepLid;
use CsrDelft\model\entity\groepen\Activiteit;
use CsrDelft\model\entity\groepen\ActiviteitSoort;
use CsrDelft\model\entity\groepen\GroepKeuzeSelectie;
use CsrDelft\model\entity\groepen\GroepStatus;
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
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * AbstractGroepenController.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
abstract class AbstractGroepenController {
	use QueryParamTrait;

	/** @var DataTable */
	protected $table;
	/** @var AbstractGroepenModel */
	protected $model;
	protected $view;

	public function __construct($model = null) {
		$this->model = $model;
	}

	/**
	 * Alle routes die groepen controllers aan gaan @return RouteCollection
	 * @see config/routes/groepen.yaml
	 */
	public function loadRoutes() {
		$routes = new RouteCollection();
		$prefix = 'groep-' . $this->model::getNaam();

		$className = get_class($this);

		$route = function ($path, $func, $methods, $defaults = [], $requirements = []) use ($routes, $prefix, $className) {
			$defaults['_mag'] = P_LOGGED_IN;
			$defaults['_controller'] = $className . '::' . $func;
			$routes->add(
				$prefix . '-' . $func,
				(new Route($path))
					->setDefaults($defaults)
					->setRequirements($requirements)
					->setMethods($methods)
			);
		};

		$route('/', 'overzicht', ['GET']);
		$route('/{id}/deelnamegrafiek', 'deelnamegrafiek', ['POST']);
		$route('/{id}/omschrijving', 'omschrijving', ['POST']);
		$route('/{id}/pasfotos', 'pasfotos', ['POST']);
		$route('/{id}/lijst', 'lijst', ['POST']);
		$route('/{id}/stats', 'stats', ['POST']);
		$route('/{id}/emails', 'emails', ['POST']);
		$route('/{id}/eetwens', 'eetwens', ['POST']);
		$route('/{id}/aanmelden/{uid}', 'aanmelden', ['POST'], ['uid' => null], ['uid' => '.{4}']);
		$route('/{id}/aanmelden2/{uid}', 'aanmelden2', ['POST'], [], ['uid' => '.{4}']);
		$route('/{id}/naar_ot/{uid}', 'naar_ot', ['POST'], ['uid' => null], ['uid' => '.{4}']);
		$route('/{id}/bewerken/{uid}', 'bewerken', ['POST'], ['uid' => null], ['uid' => '.{4}']);
		$route('/{id}/afmelden/{uid}', 'afmelden', ['POST'], ['uid' => null], ['uid' => '.{4}']);
		$route('/zoeken', 'zoeken', ['GET']);
		$route('/{id}/leden', 'leden', ['GET', 'POST']);
		$route('/beheren/{soort}', 'beheren', ['GET', 'POST'], ['soort' => null]);
		$route('/{id}/wijzigen', 'wijzigen', ['GET', 'POST'], ['id' => null]);
		$route('/{id}/logboek', 'logboek', ['POST'], ['id' => null]);
		$route('/nieuw/{soort}', 'nieuw', ['GET', 'POST'], ['soort' => null]);
		$route('/aanmaken/{soort}', 'aanmaken', ['GET', 'POST'], ['soort' => null]);
		$route('/verwijderen', 'verwijderen', ['POST']);
		$route('/opvolging', 'opvolging', ['POST']);
		$route('/converteren', 'converteren', ['POST']);
		$route('/sluiten', 'sluiten', ['POST']);
		$route('/{id}/voorbeeld', 'voorbeeld', ['POST']);
		$route('/zoeken/{zoekterm}', 'zoeken', ['GET'], ['zoekterm' => null]);
		$route('/{id}', 'bekijken', ['GET']);

		$routes->addPrefix('/groepen/' . $this->model::getNaam());
		return $routes;
	}

	public function overzicht($soort = null) {
		if ($soort) {
			$groepen = $this->model->find('status = ? AND soort = ?', [GroepStatus::HT, $soort]);
		} else {
			$groepen = $this->model->find('status = ?', [GroepStatus::HT]);
		}
		$body = new GroepenView($this->model, $groepen, $soort); // controleert rechten bekijken per groep
		return view('default', ['content' => $body]);
	}

	public function bekijken($id) {
		$groep = $this->model->get($id);
		$groepen = $this->model->find('familie = ?', [$groep->familie]);
		if (property_exists($groep, 'soort')) {
			$soort = $groep->soort;
		} else {
			$soort = null;
		}
		$body = new GroepenView($this->model, $groepen, $soort, $groep->id); // controleert rechten bekijken per groep
		return view('default', ['content' => $body]);
	}

	public function deelnamegrafiek($id) {
		$groep = $this->model->get($id);
		/** @var AbstractGroep[] $groepen */
		$groepen = $this->model->find('familie = ?', [$groep->familie]);
		return new GroepenDeelnameGrafiek($groepen); // controleert GEEN rechten bekijken
	}

	public function omschrijving($id) {
		$groep = $this->model->get($id);
		if (!$groep->mag(AccessAction::Bekijken)) {
			throw new CsrToegangException();
		}
		return new GroepOmschrijvingView($groep);
	}

	public function pasfotos($id) {
		$groep = $this->model->get($id);
		if (!$groep->mag(AccessAction::Bekijken)) {
			throw new CsrToegangException();
		}
		return new GroepPasfotosView($groep);
	}

	public function lijst($id) {
		$groep = $this->model->get($id);
		if (!$groep->mag(AccessAction::Bekijken)) {
			throw new CsrToegangException();
		}
		return new GroepLijstView($groep);
	}

	public function stats($id) {
		$groep = $this->model->get($id);
		if (!$groep->mag(AccessAction::Bekijken)) {
			throw new CsrToegangException();
		}
		return new GroepStatistiekView($groep);
	}

	public function emails($id) {
		$groep = $this->model->get($id);
		if (!$groep->mag(AccessAction::Bekijken)) {
			throw new CsrToegangException();
		}
		return new GroepEmailsView($groep);
	}

	public function eetwens($id) {
		$groep = $this->model->get($id);
		if (!$groep->mag(AccessAction::Bekijken)) {
			throw new CsrToegangException();
		}
		return new GroepEetwensView($groep);
	}

	public function zoeken($zoekterm = null) {
		if (!$zoekterm && !$this->hasParam('q')) {
			throw new CsrToegangException();
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
			/** @var AbstractGroep $groep */
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
		return new JsonResponse($result);
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
			if ($groep instanceof Activiteit AND empty($groep->rechten_aanmelden)) {
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
				throw new CsrToegangException();
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
		$form = new GroepForm($groep, $this->model->getUrl() . 'aanmaken', AccessAction::Aanmaken); // checks rechten aanmaken
		if ($this->getMethod() == 'GET') {
			$this->beheren();
			$form->setDataTableId($this->table->getDataTableId());
			return view('default', ['content' => $this->table, 'modal' => $form]);
		} elseif ($form->validate()) {
			ChangeLogModel::instance()->log($groep, 'create', null, print_r($groep, true));
			$this->model->create($groep);
			$response[] = $groep;
			if ($old) {
				$old->status = GroepStatus::OT;
				$this->model->update($old);
				$response[] = $old;
			}
			$view = new GroepenBeheerData($response);
			setMelding(get_class($groep) . ' succesvol aangemaakt!', 1);
			$form = new GroepPreviewForm($groep);
			$view->modal = $form->getHtml();
			return $view;
		} else {
			return $form;
		}
	}

	public function beheren($soort = null) {
		if ($this->getMethod() == 'POST') {
			if ($soort) {
				$groepen = $this->model->find('soort = ?', [$soort]);
			} else {
				$groepen = $this->model->find();
			}
			return new GroepenBeheerData($groepen); // controleert GEEN rechten bekijken
		} else {
			$table = new GroepenBeheerTable($this->model);
			$this->table = $table;
			return view('default', ['content' => $table]);
		}
	}

	public function wijzigen($id = null) {
		if ($id) {
			$groep = $this->model->get($id);
			if (!$groep->mag(AccessAction::Wijzigen)) {
				throw new CsrToegangException();
			}
			$form = new GroepForm($groep, $groep->getUrl() . '/wijzigen', AccessAction::Wijzigen); // checks rechten wijzigen
			if ($this->getMethod() == 'GET') {
				$this->beheren();
				$this->table->filter = $groep->naam;
				$form->setDataTableId($this->table->getDataTableId());
				return view('default', ['content' => $this->table, 'modal' => $form]);
			} elseif ($form->validate()) {
				ChangeLogModel::instance()->logChanges($form->diff());
				$this->model->update($groep);
				return new GroepenBeheerData([$groep]);
			} else {
				return $form;
			}
		} // beheren
		else {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			if (empty($selection)) {
				throw new CsrToegangException();
			}
			/** @var AbstractGroep $groep */
			$groep = $this->model->retrieveByUUID($selection[0]);
			if (!$groep OR !$groep->mag(AccessAction::Wijzigen)) {
				throw new CsrToegangException();
			}
			$form = new GroepForm($groep, $groep->getUrl() . '/wijzigen', AccessAction::Wijzigen); // checks rechten wijzigen
			if ($form->validate()) {
				ChangeLogModel::instance()->logChanges($form->diff());
				$this->model->update($groep);
				return new GroepenBeheerData([$groep]);
			} else {
				return $form;
			}
		}
	}

	public function verwijderen() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
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
		return new RemoveRowsResponse($response);
	}

	public function opvolging() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		/** @var AbstractGroep $groep */
		$groep = $this->model->retrieveByUUID($selection[0]);
		$form = new GroepOpvolgingForm($groep, $this->model->getUrl() . 'opvolging');
		if ($form->validate()) {
			$values = $form->getValues();
			$response = [];
			foreach ($selection as $UUID) {
				/** @var AbstractGroep $groep */
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
			return new GroepenBeheerData($response);
		} else {
			return $form;
		}
	}

	public function converteren() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
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
				return new RemoveRowsResponse($response);
			} else {
				return new GroepenBeheerData($response);
			}
		} else {
			return $form;
		}
	}

	public function sluiten() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
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
		return new GroepenBeheerData($response);
	}

	public function voorbeeld($id) {
		/** @var AbstractGroep $groep */
		$groep = $this->model->retrieveByUUID($id);
		if (!$groep OR !$groep->mag(AccessAction::Bekijken)) {
			throw new CsrToegangException();
		}
		return new GroepPreviewForm($groep);
	}

	/**
	 * @param $id
	 */
	public function logboek($id) {
		$groep = $this->model->get($id);
		// data request
		if ($groep) {
			if (!$groep->mag(AccessAction::Bekijken)) {
				throw new CsrToegangException();
			}
			$data = ChangeLogModel::instance()->find('subject = ?', [$groep->getUUID()]);
			return new GroepLogboekData($data);
		} // popup request
		else {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			/** @var AbstractGroep $groep */
			$groep = $this->model->retrieveByUUID($selection[0]);
			if (!$groep || !$groep->mag(AccessAction::Bekijken)) {
				throw new CsrToegangException('Kan logboek niet vinden', 403);
			}
			return new GroepLogboekForm($groep);
		}
	}

	public function leden($id) {
		$groep = $this->model->get($id);
		if (!$groep->mag(AccessAction::Bekijken)) {
			throw new CsrToegangException();
		}
		if ($this->getMethod() == 'POST') {
			return new GroepLedenData($groep::getLedenModel()->getLedenVoorGroep($groep));
		} else {
			return new GroepLedenTable($groep::getLedenModel(), $groep);
		}
	}

	public function aanmelden2(AbstractGroep $groep, $uid) {
		$model = $groep::getLedenModel();

		if (!$groep->mag(AccessAction::Aanmelden)) {
			throw new CsrToegangException();
		}
		$lid = $model->nieuw($groep, $uid);

		$opmerking = $this->getPost('opmerking2');

		$keuzes = [];
		foreach ($opmerking as $keuze) {
			$keuzes[] = new GroepKeuzeSelectie($keuze['naam'], $keuze['selectie']);
		}

		if (!$groep->valideerOpmerking($keuzes)) {
			throw new CsrToegangException('', 400);
		}

		$lid->opmerking2 = $keuzes;

		ChangeLogModel::instance()->log($groep, 'aanmelden', null, $lid->uid);
		$model->create($lid);

		return new JsonResponse(['success' => true]);
	}

	public function aanmelden(AbstractGroep $groep, $uid = null) {
		$model = $groep::getLedenModel();
		if ($uid) {
			if (!$groep->mag(AccessAction::Aanmelden)) {
				throw new CsrToegangException();
			}
			$lid = $model->nieuw($groep, $uid);
			$form = new GroepAanmeldenForm($lid, $groep);
			if ($form->validate()) {
				ChangeLogModel::instance()->log($groep, 'aanmelden', null, $lid->uid);
				$model->create($lid);
				return new GroepPasfotosView($groep);
			} else {
				return $form;
			}
		} // beheren
		else {
			if (!$groep->mag(AccessAction::Beheren)) {
				throw new CsrToegangException();
			}
			$lid = $model->nieuw($groep, null);
			$leden = group_by_distinct('uid', $groep->getLeden());
			$form = new GroepLidBeheerForm($lid, $groep->getUrl() . '/aanmelden', array_keys($leden));
			if ($form->validate()) {
				ChangeLogModel::instance()->log($groep, 'aanmelden', null, $lid->uid);
				$model->create($lid);
				return new GroepLedenData([$lid]);
			} else {
				return $form;
			}
		}
	}

	public function bewerken($id, $uid = null) {
		$groep = $this->model->get($id);
		$model = $groep::getLedenModel();
		if ($uid) {
			if (!$groep->mag(AccessAction::Bewerken)) {
				throw new CsrToegangException();
			}
			$lid = $model->get($groep, $uid);
			$form = new GroepBewerkenForm($lid, $groep);
			if ($form->validate()) {
				ChangeLogModel::instance()->logChanges($form->diff());
				$model->update($lid);
			}
			return $form;
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
				throw new CsrToegangException();
			}
			if (!$groep->mag(AccessAction::Beheren)) {
				throw new CsrToegangException();
			}
			$form = new GroepLidBeheerForm($lid, $groep->getUrl() . '/bewerken');
			if ($form->validate()) {
				ChangeLogModel::instance()->logChanges($form->diff());
				$model->update($lid);
				return new GroepLedenData([$lid]);
			} else {
				return $form;
			}
		}
	}

	public function afmelden($id, $uid = null) {
		$groep = $this->model->get($id);
		$model = $groep::getLedenModel();
		if ($uid) {
			if (!$groep->mag(AccessAction::Afmelden) AND !$groep->mag(AccessAction::Beheren)) { // A::Beheren voor afmelden via context-menu
				throw new CsrToegangException();
			}
			$lid = $model->get($groep, $uid);
			ChangeLogModel::instance()->log($groep, 'afmelden', $lid->uid, null);
			$model->delete($lid);
			return new GroepView($groep);
		} // beheren
		else {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			if (empty($selection)) {
				throw new CsrToegangException();
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
			return new RemoveRowsResponse($response);
		}
	}

	public function naar_ot($id, $uid = null) {
		$groep = $this->model->get($id);
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
			return new GroepView($groep);
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
			return new RemoveRowsResponse($response);
		}

	}

}
