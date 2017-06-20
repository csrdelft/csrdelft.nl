<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\entity\fiscaat\CiviSaldo;
use CsrDelft\model\fiscaat\CiviBestellingModel;
use CsrDelft\model\fiscaat\CiviSaldoModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\fiscaat\BeheerCiviSaldoView;
use CsrDelft\view\fiscaat\BeheerSaldoResponse;
use CsrDelft\view\fiscaat\InleggenForm;
use CsrDelft\view\fiscaat\LidRegistratieForm;
use CsrDelft\view\formulier\datatable\RemoveRowsResponse;
use function CsrDelft\getDateTime;

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
				'overzicht' => 'P_MAAL_MOD',
				'registreren' => 'P_MAAL_MOD',
				'verwijderen' => 'P_MAAL_MOD',
				'inleggen' => 'P_MAAL_MOD'
			];
		} else {
			$this->acl = [
				'overzicht' => 'P_MAAL_MOD',
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
		$this->view = new CsrLayoutPage(new BeheerCiviSaldoView());
	}

	public function POST_overzicht() {
		$this->view = new BeheerSaldoResponse($this->model->find('deleted = false'));
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

					$bestelling = $bestelling_model->vanInleg($inleg, $civisaldo->uid);
					$bestelling_model->create($bestelling);

					$this->model->ophogen($civisaldo->uid, $inleg);
					$civisaldo->saldo += $inleg;
					$civisaldo->laatst_veranderd = getDateTime();
				});

				$this->view = new BeheerSaldoResponse(array($civisaldo));
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
			$saldo = $form->getModel();
			$saldo->laatst_veranderd = date_create()->format(DATE_ISO8601);
			if ($this->model->find('uid = ?', [$saldo->uid])->rowCount() === 1) {
				$this->model->update($saldo);
			} else {
				$this->model->create($saldo);
			}
			$this->view = new BeheerSaldoResponse(array($saldo));
			return;
		}

		$this->view = $form;
	}
}
