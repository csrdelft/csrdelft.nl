<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\controller\framework\AclController;
use CsrDelft\model\eetplan\EetplanBekendenModel;
use CsrDelft\model\eetplan\EetplanModel;
use CsrDelft\model\entity\eetplan\Eetplan;
use CsrDelft\model\entity\eetplan\EetplanBekenden;
use CsrDelft\model\entity\groepen\GroepStatus;
use CsrDelft\model\entity\groepen\Woonoord;
use CsrDelft\model\groepen\LichtingenModel;
use CsrDelft\model\groepen\WoonoordenModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\datatable\RemoveRowsResponse;
use CsrDelft\view\eetplan\EetplanBekendeHuizenForm;
use CsrDelft\view\eetplan\EetplanBekendeHuizenResponse;
use CsrDelft\view\eetplan\EetplanBekendeHuizenTable;
use CsrDelft\view\eetplan\EetplanBekendenForm;
use CsrDelft\view\eetplan\EetplanBekendenTable;
use CsrDelft\view\eetplan\EetplanHuizenResponse;
use CsrDelft\view\eetplan\EetplanHuizenTable;
use CsrDelft\view\eetplan\EetplanHuizenZoekenResponse;
use CsrDelft\view\eetplan\EetplanRelatieResponse;
use CsrDelft\view\eetplan\NieuwEetplanForm;
use CsrDelft\view\eetplan\VerwijderEetplanForm;
use CsrDelft\view\View;

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
	 * @throws CsrException
	 */
	public function performAction(array $args = array()) {
		$this->action = 'view';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}

		$this->lichting = substr((string)LichtingenModel::getJongsteLidjaar(), 2, 2);

		$this->view = parent::performAction($this->getParams(3));
	}

	public function view() {
		return view('eetplan.overzicht', [
			'eetplan' => $this->model->getEetplan($this->lichting)
		]);
	}

	/**
	 * @param null $uid
	 * @return View
	 * @throws CsrToegangException
	 */
	public function noviet($uid = null) {
		$eetplan = $this->model->getEetplanVoorNoviet($uid);
		if ($eetplan === false) {
			throw new CsrToegangException("Geen eetplan gevonden voor deze noviet", 404);
		}

		return view('eetplan.noviet', [
			'noviet' => ProfielModel::get($uid),
			'eetplan' => $this->model->getEetplanVoorNoviet($uid)
		]);
	}

	public function huis($id = null) {
		$eetplan = $this->model->getEetplanVoorHuis($id, $this->lichting);
		if ($eetplan === false) {
			$this->exit_http(403);
		}

		return view('eetplan.huis', [
			'woonoord' => WoonoordenModel::get($id),
			'eetplan' => $this->model->getEetplanVoorHuis($id, $this->lichting)
		]);
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
			return new EetplanHuizenResponse($woonoorden);
		} else {
			$woonoorden = WoonoordenModel::instance()->find('status = ?', array(GroepStatus::HT));
			return new EetplanHuizenResponse($woonoorden);
		}
	}

	/**
	 * @param null $actie
	 * @return View
	 * @throws CsrToegangException
	 */
	public function bekendehuizen($actie = null) {
		if ($this->getMethod() == 'POST') {
			if ($actie == 'toevoegen') {
				$eetplan = new Eetplan();
				$eetplan->avond = '0000-00-00';
				$form = new EetplanBekendeHuizenForm($eetplan);
				if (!$form->validate()) {
					return $form;
				} elseif ($this->model->exists($eetplan)) {
					setMelding('Deze noviet is al eens op dit huis geweest', -1);
					return $form;
				} else {
					$this->model->create($eetplan);
					return new EetplanBekendeHuizenResponse($this->model->getBekendeHuizen($this->lichting));
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
				return new RemoveRowsResponse($verwijderd);
			} else {
				return new EetplanBekendeHuizenResponse($this->model->getBekendeHuizen($this->lichting));
			}
		} else {
			if ($actie == 'zoeken') {
				$huisnaam = filter_input(INPUT_GET, 'q');
				$huisnaam = '%' . $huisnaam . '%';
				$woonoorden = WoonoordenModel::instance()->find('status = ? AND naam LIKE ?', array(GroepStatus::HT, $huisnaam))->fetchAll();
				return new EetplanHuizenZoekenResponse($woonoorden);
			} else {
				throw new CsrToegangException('Mag alleen bekende huizen zoeken', 403);
			}
		}
	}


	public function novietrelatie($actie = null) {
		$model = EetplanBekendenModel::instance();
		if ($actie == 'toevoegen') {
			$eetplanbekenden = new EetplanBekenden();
			$form = new EetplanBekendenForm($eetplanbekenden);
			if (!$form->validate()) {
				return $form;
			} elseif (EetplanBekendenModel::instance()->exists($eetplanbekenden)) {
				setMelding('Bekenden bestaan al', -1);
				return $form;
			} else {
				$model->create($eetplanbekenden);
				return new EetplanRelatieResponse($model->getBekenden($this->lichting));
			}
		} elseif ($actie == 'verwijderen') {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			$verwijderd = array();
			foreach ($selection as $uuid) {
				$bekenden = $model->retrieveByUUID($uuid);
				EetplanBekendenModel::instance()->delete($bekenden);
				$verwijderd[] = $bekenden;
			}
			return new RemoveRowsResponse($verwijderd);
		} else {
			return new EetplanRelatieResponse($model->getBekenden($this->lichting));
		}
	}

	/**
	 * Beheerpagina.
	 *
	 * POST een json body om dingen te doen.
	 */
	public function beheer() {
		return view('eetplan.beheer', [
			'bekendentable' => new EetplanBekendenTable(),
			'huizentable' => new EetplanHuizenTable(),
			'bekendehuizentable' => new EetplanBekendeHuizenTable(),
			'eetplan' => $this->model->getEetplan($this->lichting)
		]);
	}

	public function nieuw() {
		$form = new NieuwEetplanForm();

		if (!$form->validate()) {
			return $form;
		} elseif ($this->model->count("avond = ?", array($form->getValues()['avond'])) > 0) {
			setMelding('Er bestaat al een eetplan met deze datum', -1);
			return $form;
		} else {
			$avond = $form->getValues()['avond'];
			$eetplan = $this->model->maakEetplan($avond, $this->lichting);

			foreach ($eetplan as $sessie) {
				$this->model->create($sessie);
			}

			return view('eetplan.table', ['eetplan' => $this->model->getEetplan($this->lichting)]);
		}
	}

	public function verwijderen() {
		$avonden = $this->model->getAvonden($this->lichting);
		$form = new VerwijderEetplanForm($avonden);

		if (!$form->validate()) {
			return $form;
		} else {
			$avond = $form->getValues()['avond'];
			$this->model->verwijderEetplan($avond, $this->lichting);

			return view('eetplan.table', ['eetplan' => $this->model->getEetplan($this->lichting)]);
		}
	}
}
