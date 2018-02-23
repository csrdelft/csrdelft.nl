<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\fiscaat\pin\PinTransactieMatchModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\fiscaat\pin\PinTransactieMatchTableResponse;
use CsrDelft\view\fiscaat\pin\PinTransactieOverzichtView;

/**
 * Class PinTransactieController
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 19/09/2017
 */
class PinTransactieController extends AclController {

	public function __construct($query) {
		parent::__construct($query, PinTransactieMatchModel::instance());

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
		return parent::performAction($args);
	}

	public function GET_overzicht() {
		$this->view = new CsrLayoutPage(new PinTransactieOverzichtView());
	}

	public function POST_overzicht() {
		$filter = $this->hasParam('filter') ? $this->getParam('filter') : '';

		switch ($filter) {
			case 'metFout':
				$data = $this->model->find('reden <> \'match\'');
				break;

			case 'alles':
			default:
				$data = $this->model->find();
				break;
		}

		$this->view = new PinTransactieMatchTableResponse($data);
	}
}
