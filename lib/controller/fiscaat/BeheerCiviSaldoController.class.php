<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\entity\fiscaat\CiviSaldo;
use CsrDelft\model\fiscaat\CiviBestellingModel;
use CsrDelft\model\fiscaat\CiviSaldoModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\ProfielService;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\view\datatable\RemoveRowsResponse;
use CsrDelft\view\fiscaat\saldo\CiviSaldoTable;
use CsrDelft\view\fiscaat\saldo\CiviSaldoTableResponse;
use CsrDelft\view\fiscaat\saldo\InleggenForm;
use CsrDelft\view\fiscaat\saldo\LidRegistratieForm;
use CsrDelft\view\fiscaat\saldo\SaldiSomForm;
use CsrDelft\view\JsonResponse;
use DateTime;

/**
 * BeheerCiviSaldoController.class.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/04/2017
 *
 * @property CiviSaldoModel $model
 */
class BeheerCiviSaldoController extends AclController {
	public function __construct($query) {
		parent::__construct($query, CiviSaldoModel::instance());

		if ($this->getMethod() == "POST") {
			$this->acl = [
				'overzicht' => 'P_FISCAAT_READ',
				'registreren' => 'P_FISCAAT_MOD',
				'verwijderen' => 'P_FISCAAT_MOD',
				'inleggen' => 'P_FISCAAT_MOD',
				'som' => 'P_FISCAAT_READ',
			];
		} else {
			$this->acl = [
				'overzicht' => 'P_FISCAAT_READ',
				'zoek' => 'P_FISCAAT_READ',
			];
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'overzicht';

		if ($this->hasParam(3)) {
			$this->action = $this->getParam(3);
		}
		return parent::performAction($this->getParams(4));
	}

	public function GET_overzicht() {
		$this->view = view('fiscaat.pagina', [
			'titel' => 'Saldo beheer',
			'view' => new CiviSaldoTable(),
		]);
	}

	public function POST_overzicht() {
		$this->view = new CiviSaldoTableResponse($this->model->find('deleted = false'));
	}

	public function POST_inleggen() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);

		/** @var CiviSaldo $civisaldo */
		$civisaldo = $this->model->retrieveByUUID($selection[0]);

		if ($civisaldo) {
			$form = new InleggenForm($civisaldo);
			$values = $form->getValues();
			if ($form->validate() AND $values['inleg'] !== 0 AND $values['saldo'] == $civisaldo->saldo) {
				$inleg = $values['inleg'];
				Database::transaction(function () use ($inleg, $civisaldo) {
					$bestelling_model = CiviBestellingModel::instance();

					$bestelling = $bestelling_model->vanBedragInCenten($inleg, $civisaldo->uid);
					$bestelling_model->create($bestelling);

					$this->model->ophogen($civisaldo->uid, $inleg);
					$civisaldo->saldo += $inleg;
					$civisaldo->laatst_veranderd = getDateTime();
				});

				$this->view = new CiviSaldoTableResponse(array($civisaldo));
			} else {
				$this->view = $form;
			}

			return;
		}

		$this->exit_http(404);
	}

	public function POST_verwijderen() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);

		$removed = array();
		foreach ($selection as $uuid) {
			$civisaldo = $this->model->retrieveByUUID($uuid);

			if ($civisaldo) {
				$civisaldo->deleted = true;
				$this->model->update($civisaldo);
				$removed[] = $civisaldo;
			}
		}

		if (!empty($removed)) {
			$this->view = new RemoveRowsResponse($removed);
			return;
		}

		$this->exit_http(404);
	}

	public function POST_registreren() {
		$form = new LidRegistratieForm(new CiviSaldo());

		if ($form->validate()) {
			/** @var CiviSaldo $saldo */
			$saldo = $form->getModel();
			$saldo->laatst_veranderd = date_create()->format(DATE_ISO8601);

			if (is_null($saldo->uid)) {
				$laatsteSaldo = $this->model->find("uid LIKE 'c%'", [], null, 'uid DESC', 1)->fetch();
				$saldo->uid = ++$laatsteSaldo->uid;
			}

			if (is_null($saldo->naam)) {
				$saldo->naam = '';
			}

			if ($this->model->find('uid = ?', [$saldo->uid])->rowCount() === 1) {
				$this->exit_http(403);
			} else {
				$saldo->id = $this->model->create($saldo);
			}

			$this->view = new CiviSaldoTableResponse(array($saldo));
			return;
		}

		$this->view = $form;
	}

	public function POST_som() {
		$momentString = filter_input(INPUT_POST, 'moment', FILTER_SANITIZE_STRING);
		$moment = DateTime::createFromFormat("Y-m-d H:i:s", $momentString);
		if (!$moment) {
			$this->exit_http(400);
		}

		$this->view = view('fiscaat.saldisom', [
			'saldisomform' => new SaldiSomForm($this->model, $moment),
			'saldisom' => $this->model->getSomSaldiOp($moment),
			'saldisomleden' => $this->model->getSomSaldiOp($moment, true),
		]);
	}

	public function GET_zoek() {
		$zoekterm = $this->getParam('q');

		$pdo = Database::instance()->getDatabase();

		$leden = ProfielService::instance()->zoekLeden($zoekterm, 'naam', 'alle', 'achternaam');
		$uids = array_map(function ($profiel) use ($pdo) { return $pdo->quote($profiel->uid); }, $leden);

		if (count($uids) > 0) {
			$whereUids = ' OR uid IN ('. join(', ', $uids) .')';
		} else {
			$whereUids = '';
		}

		$civiSaldi = $this->model->find('deleted <> 1 AND (uid LIKE :zoekTerm OR naam LIKE :zoekTerm' . $whereUids . ')', [':zoekTerm' => sql_contains($zoekterm)])->fetchAll();


		$resp = [];
		foreach ($civiSaldi as $civiSaldo) {
			$profiel = ProfielModel::get($civiSaldo->uid);
			$resp[] = [
				'label' => $profiel === false ? $civiSaldo->naam : $profiel->getNaam('volledig'),
				'value' => $civiSaldo->uid
			];
		}

		$this->view = new JsonResponse($resp);
	}
}
