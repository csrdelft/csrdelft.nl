<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\common\CsrException;
use CsrDelft\controller\framework\AclController;
use CsrDelft\model\fiscaat\pin_transacties\PinTransactieModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\fiscaat\pin_transacties\PinTransactieTableResponse;
use CsrDelft\view\fiscaat\pin_transacties\PinTransactieOverzichtView;

/**
 * Class PinTransactieController
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 19/09/2017
 */
class PinTransactieController extends AclController {

	public function __construct($query) {
		parent::__construct($query, PinTransactieModel::instance());

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

	/**
	 * @param array $args
	 * @return mixed
	 * @throws CsrException
	 */
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
			case 'alles':
				$data = $this->model->find();
				break;
			default:
				$data = $this->model->find('bestelling_id IS NULL');
				break;
		}

		$this->view = new PinTransactieTableResponse($data);
	}
}
