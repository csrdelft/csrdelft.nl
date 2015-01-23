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

		//return $this->converteren();

		if ($this->hasParam(3)) { // id or action
			$this->action = $this->getParam(3);
		} else {
			$this->action = 'overzicht'; // default
		}

		switch ($this->action) {
			// geen groep id vereist
			case 'overzicht':
			case A::Beheren:
			case A::Wijzigen:
			case A::Verwijderen:
				break;

			case A::Aanmaken:
				if (LoginModel::mag('P_LEDEN_MOD')) {
					break;
				}
				$model = $this->model;
				$algemeen = AccessModel::get($model::orm, $this->action, '*');
				if ($algemeen AND LoginModel::mag($algemeen)) {
					break;
				}
				if ($this->hasParam(3)) { // soort
					$soort = AccessModel::get($model::orm, $this->action, $this->getParam(3));
					if ($soort AND LoginModel::mag($soort)) {
						$args[] = $soort;
						break;
					}
				}
				$this->geentoegang();

			// groep id vereist
			default:
				$id = (int) $this->action; // id
				$groep = $this->model->get($id);
				if (!$groep) {
					$this->geentoegang();
				}
				$args[] = $groep;
				$uid = null;
				if ($this->hasParam(4)) { // action
					$this->action = $this->getParam(4);
					if ($this->hasParam(5)) { // uid
						$uid = $this->getParam(5);
						$args[] = $uid;
					}
				} else {
					$this->action = A::Bekijken; // default
				}
				if (!$groep->mag($this->action, $uid)) {
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
			case A::Rechten:
			case A::Beheren:
			case 'leden':
				return true;

			case 'overzicht':
			case A::Bekijken:
				return $method === 'GET';

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
				return $method === 'POST';

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
			$class = $groep::leden;
			$this->view = new GroepLedenTable($class::instance(), $groep);
		}
	}

	public function aanmelden(Groep $groep, $uid = null) {
		$class = $groep::leden;
		$model = $class::instance();
		if ($uid) {
			$lid = $model->instance()->nieuw($groep, LoginModel::getUid());
			$form = new GroepLidForm($lid, $groep->getSuggesties());
			if ($form->validate()) {
				$model->create($lid);
			}
			$this->view = $form;
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
		$class = $groep::leden;
		$model = $class::instance();
		if ($uid) {
			$lid = $model->get($groep, $uid);
			$form = new GroepLidForm($lid, $groep->getSuggesties());
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
		$class = $groep::leden;
		$model = $class::instance();
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

	public function converteren() {

		$totaalGroepen = 0;
		$totaalLeden = 0;

		$typeModel = DynamicEntityModel::makeModel('groeptype');
		$groepModel = DynamicEntityModel::makeModel('groep');
		$lidModel = DynamicEntityModel::makeModel('groeplid');

		foreach ($typeModel->find() as $type) {

			$soort = null;

			switch ($type->naam) {

				case 'Commissies': $model = CommissiesModel::instance();
					$soort = CommissieSoort::Commissie;
					break;
				case 'SjaarCies': $model = CommissiesModel::instance();
					$soort = CommissieSoort::SjaarCie;
					break;

				case 'OWee': $model = ActiviteitenModel::instance();
					$soort = ActiviteitSoort::OWee;
					break;
				case 'Dies': $model = ActiviteitenModel::instance();
					$soort = ActiviteitSoort::Dies;
					break;
				case 'Sjaarsacties': $model = ActiviteitenModel::instance();
					$soort = ActiviteitSoort::SjaarActie;
					break;

				case 'Overig': $model = GroepenModel::instance();
					break;
				case 'wikigroepen': $model = GroepenModel::instance();
					break;

				case 'Woonoorden': $model = WoonoordenModel::instance();
					break;

				case 'Onderverenigingen': $model = OnderverenigingenModel::instance();
					break;

				case 'Werkgroepen': $model = WerkgroepenModel::instance();
					break;

				case 'Besturen': $model = BesturenModel::instance();
					break;

				case 'Ketzers': $model = KetzersModel::instance();
					break;

				default:
					echo 'skipped type ' . $type->naam;
					exit;
			}

			if (true) {
				$admin = DatabaseAdmin::instance();

				$class = $model::orm;
				$orm = new $class();
				$query = $admin->prepare('TRUNCATE TABLE ' . $orm->getTableName());
				$query->execute();

				$query = $admin->prepare('UPDATE groep SET omnummering = 0 WHERE gtype = ' . $type->id);
				$query->execute();

				$leden = $orm::leden;
				$class = $leden::orm;
				require_once 'model/entity/groepen/' . $class . '.class.php';
				$orm = new $class();
				$query = $admin->prepare('TRUNCATE TABLE ' . $orm->getTableName());
				$query->execute();
			}

			$class = $model::orm;
			$orm = new $class();
			$leden = $orm::leden;

			$naam = str_replace('Model', '', get_class($model));
			if (!CmsPaginaModel::get($naam)) {
				$pagina = CmsPaginaModel::instance()->nieuw($naam);
				$pagina->inhoud = $type->beschrijving;
				$pagina->rechten_bekijken = $type->groepenAanmaakbaar;
				CmsPaginaModel::instance()->create($pagina);
			}

			foreach ($groepModel->find('gtype = ?', array($type->id)) as $groep) {

				$entity = $model->nieuw();

				$entity->naam = $groep->naam;
				$entity->samenvatting = $groep->sbeschrijving;
				$entity->omschrijving = $groep->beschrijving;
				$entity->begin_moment = $groep->begin;
				if ($groep->einde === '0000-00-00') {
					$entity->eind_moment = null;
				} else {
					$entity->eind_moment = $groep->einde;
				}
				$entity->website = null;
				$entity->door_uid = substr($groep->eigenaar, 0, 4);
				$entity->keuzelijst = $groep->functiefilter;

				if (property_exists($entity, 'soort')) {
					if ($soort !== null) {
						$entity->soort = $soort;
					} else {
						echo 'skipped soort ' . $soort . ' ' . $groep->id . ' ' . $groep->naam;
						exit;
					}
				}

				if (property_exists($entity, 'opvolg_naam')) {
					$entity->opvolg_naam = $groep->snaam;
					$entity->status = $groep->status;

					$jaargang = array();
					preg_match('/\d{4}-\d{4}/', $groep->naam, $jaargang);
					if (isset($jaargang[0])) {
						$entity->jaargang = $jaargang[0];
					} else {
						$entity->jaargang = '';
					}
				}

				if (property_exists($entity, 'aanmeld_limiet')) {
					$entity->aanmeld_limiet = $groep->limiet;
					$entity->aanmelden_vanaf = $entity->begin_moment;
					if ($entity->eind_moment === null) {
						$entity->aanmelden_tot = $entity->begin_moment;
					} else {
						$entity->aanmelden_tot = $entity->eind_moment;
					}
				}

				if (property_exists($entity, 'bijbeltekst')) {
					$entity->bijbeltekst = '';
				}

				$model->create($entity);

				$query = $admin->prepare('UPDATE groep SET omnummering = ' . $entity->id . ' WHERE id = ' . $groep->id);
				$query->execute();

				$totaalGroepen++;

				foreach ($lidModel->find('groepid = ?', array($groep->id)) as $groeplid) {

					$lid = $leden::instance()->nieuw($entity, $groeplid->uid);

					$lid->door_uid = $groeplid->uid;
					$lid->lid_sinds = $groeplid->moment;
					$lid->opmerking = $groeplid->functie;

					$leden::instance()->create($lid);

					$totaalLeden++;
				}

				echo 'leden: ' . $totaalLeden . '<br />';
			}

			echo 'groepen: ' . $totaalGroepen . '<br />';
		}
		exit;
	}

}
