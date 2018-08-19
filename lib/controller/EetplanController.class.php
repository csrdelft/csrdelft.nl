<?php

namespace CsrDelft\controller;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\eetplan\EetplanBekendenModel;
use CsrDelft\model\eetplan\EetplanModel;
use CsrDelft\model\entity\eetplan\Eetplan;
use CsrDelft\model\entity\eetplan\EetplanBekenden;
use CsrDelft\model\entity\groepen\GroepStatus;
use CsrDelft\model\entity\groepen\Woonoord;
use CsrDelft\model\groepen\LichtingenModel;
use CsrDelft\model\groepen\WoonoordenModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\eetplan\EetplanBeheerView;
use CsrDelft\view\eetplan\EetplanBekendeHuizenForm;
use CsrDelft\view\eetplan\EetplanBekendeHuizenResponse;
use CsrDelft\view\eetplan\EetplanBekendenForm;
use CsrDelft\view\eetplan\EetplanHuisView;
use CsrDelft\view\eetplan\EetplanHuizenResponse;
use CsrDelft\view\eetplan\EetplanHuizenView;
use CsrDelft\view\eetplan\EetplanNovietView;
use CsrDelft\view\eetplan\EetplanRelatieView;
use CsrDelft\view\eetplan\EetplanTableView;
use CsrDelft\view\eetplan\EetplanView;
use CsrDelft\view\eetplan\NieuwEetplanForm;
use CsrDelft\view\eetplan\VerwijderEetplanForm;
use CsrDelft\view\formulier\datatable\RemoveRowsResponse;

/**
 * EetplanController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor eetplan.
 */
class EetplanController extends AclController {
	/**
	 * @var EetplanModel
	 */
	protected $model;
	private $lichting;

	public function __construct($query) {
		parent::__construct($query, EetplanModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'view' => 'P_LEDEN_READ',
				'noviet' => 'P_LEDEN_READ',
				'huis' => 'P_LEDEN_READ',
				'beheer' => 'P_ADMIN,commissie:NovCie',
				'bekendehuizen' => 'P_ADMIN,commissie:NovCie',
				'json' => 'P_LEDEN_READ',
			);
		} else {
			$this->acl = array(
				'beheer' => 'P_ADMIN,commissie:NovCie',
				'woonoorden' => 'P_ADMIN,commissie:NovCie',
				'novietrelatie' => 'P_ADMIN,commissie:NovCie',
				'bekendehuizen' => 'P_ADMIN,commissie:NovCie',
				'nieuw' => 'P_ADMIN,commissie:NovCie',
				'verwijderen' => 'P_ADMIN,commissie:NovCie',
			);
		}
	}

	/**
	 * /eetplan/<$this->action>/<$this->lichting>|huidig/<$args>
	 *
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function performAction(array $args = array()) {
		$this->action = 'view';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}

		$this->lichting = substr((string)LichtingenModel::getJongsteLidjaar(), 2, 2);

		return parent::performAction($this->getParams(3));
	}

	public function view() {
		$body = new EetplanView($this->model, $this->lichting);
		$this->view = new CsrLayoutPage($body);
		$this->view->addCompressedResources('eetplan');
	}

	public function noviet($uid = null) {
		$eetplan = $this->model->getEetplanVoorNoviet($uid);
		if ($eetplan === false) {
			$this->exit_http(403);
		}
		$body = new EetplanNovietView($this->model->getEetplanVoorNoviet($uid), $this->lichting, $uid);
		$this->view = new CsrLayoutPage($body);
		$this->view->addCompressedResources('eetplan');
	}

	public function huis($id = null) {
		$eetplan = $this->model->getEetplanVoorHuis($id, $this->lichting);
		if ($eetplan === false) {
			$this->exit_http(403);
		}
		$body = new EetplanHuisView($this->model->getEetplanVoorHuis($id, $this->lichting), $this->lichting, $id);
		$this->view = new CsrLayoutPage($body);
		$this->view->addCompressedResources('eetplan');
	}

	public function woonoorden($actie = null) {
		if ($actie == 'aan' OR $actie == 'uit') {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			$woonoorden = array();
			foreach ($selection as $woonoord) {
				/** @var Woonoord $woonoord */
				$woonoord = WoonoordenModel::instance()->retrieveByUUID($woonoord);
				$woonoord->eetplan = $actie == 'aan';
				WoonoordenModel::instance()->update($woonoord);
				$woonoorden[] = $woonoord;
			}
			$this->view = new EetplanHuizenView($woonoorden);
		} else {
			$woonoorden = WoonoordenModel::instance()->find('status = ?', array(GroepStatus::HT));
			$this->view = new EetplanHuizenView($woonoorden);
		}
	}

	public function bekendehuizen($actie = null) {
		if ($this->getMethod() == 'POST') {
			if ($actie == 'toevoegen') {
				$eetplan = new Eetplan();
				$eetplan->avond = '0000-00-00';
				$form = new EetplanBekendeHuizenForm($eetplan);
				if (!$form->validate()) {
					$this->view = $form;
				} elseif ($this->model->exists($eetplan)) {
					setMelding('Deze noviet is al eens op dit huis geweest', -1);
					$this->view = $form;
				} else {
					$this->model->create($eetplan);
					$this->view = new EetplanBekendeHuizenResponse($this->model->getBekendeHuizen($this->lichting));
				}
			} elseif ($actie == 'verwijderen') {
				$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
				$verwijderd = array();
				if ($selection !== false) {
					foreach ($selection as $uuid) {
						$eetplan = $this->model->retrieveByUUID($uuid);
						if ($eetplan === false) continue;
						$this->model->delete($eetplan);
						$verwijderd[] = $eetplan;
					}
				}
				$this->view = new RemoveRowsResponse($verwijderd);
			} else {
				$this->view = new EetplanBekendeHuizenResponse($this->model->getBekendeHuizen($this->lichting));
			}
		} else {
			if ($actie == 'zoeken') {
				$huisnaam = filter_input(INPUT_GET, 'q');
				$huisnaam = '%' . $huisnaam . '%';
				$woonoorden = WoonoordenModel::instance()->find('status = ? AND naam LIKE ?', array(GroepStatus::HT, $huisnaam))->fetchAll();
				$this->view = new EetplanHuizenResponse($woonoorden);
			} else {
				$this->exit_http(403);
			}
		}
	}


	public function novietrelatie($actie = null) {
		$model = EetplanBekendenModel::instance();
		if ($actie == 'toevoegen') {
			$eetplanbekenden = new EetplanBekenden();
			$form = new EetplanBekendenForm($eetplanbekenden);
			if (!$form->validate()) {
				$this->view = $form;
			} elseif (EetplanBekendenModel::instance()->exists($eetplanbekenden)) {
				setMelding('Bekenden bestaan al', -1);
				$this->view = $form;
			} else {
				$model->create($eetplanbekenden);
				$this->view = new EetplanRelatieView($model->getBekenden($this->lichting));
			}
		} elseif ($actie == 'verwijderen') {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			$verwijderd = array();
			foreach ($selection as $uuid) {
				$bekenden = $model->retrieveByUUID($uuid);
				EetplanBekendenModel::instance()->delete($bekenden);
				$verwijderd[] = $bekenden;
			}
			$this->view = new RemoveRowsResponse($verwijderd);
		} else {
			$this->view = new EetplanRelatieView($model->getBekenden($this->lichting));
		}
	}

	/**
	 * Beheerpagina.
	 *
	 * POST een json body om dingen te doen.
	 */
	public function beheer() {
		$body = new EetplanBeheerView($this->model->getEetplan($this->lichting), $this->lichting);
		$this->view = new CsrLayoutPage($body);
		$this->view->addCompressedResources('eetplan');
	}

	public function nieuw() {
		$form = new NieuwEetplanForm();

		if (!$form->validate()) {
			$this->view = $form;
		} elseif ($this->model->count("avond = ?", array($form->getValues()['avond'])) > 0) {
			setMelding('Er bestaat al een eetplan met deze datum', -1);
			$this->view = $form;
		} else {
			$avond = $form->getValues()['avond'];
			$eetplan = $this->model->maakEetplan($avond, $this->lichting);

			foreach ($eetplan as $sessie) {
				$this->model->create($sessie);
			}

			$this->view = new EetplanTableView($this->model->getEetplan($this->lichting));
		}
	}

	public function verwijderen() {
		$avonden = $this->model->getAvonden($this->lichting);
		$form = new VerwijderEetplanForm($avonden);

		if (!$form->validate()) {
			$this->view = $form;
		} else {
			$avond = $form->getValues()['avond'];
			$this->model->verwijderEetplan($avond, $this->lichting);

			$this->view = new EetplanTableView($this->model->getEetplan($this->lichting));
		}
	}
}
