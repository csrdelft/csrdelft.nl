<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\fiscaat\CiviBestellingModel;
use CsrDelft\model\fiscaat\CiviCategorieModel;
use CsrDelft\view\fiscaat\CiviCategorieSuggestiesResponse;


/**
 * Class BeheerCiviBestellingController
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @property CiviBestellingModel $model
 */
class BeheerCiviCategorienController extends AclController {
	public function __construct($query) {
		parent::__construct($query, CiviCategorieModel::instance());

		if ($this->getMethod() == "POST") {
			$this->acl = [
			];
		} else {
			$this->acl = [
				'suggesties' => 'P_FISCAAT_READ',
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

	public function GET_suggesties() {
		$query = '%' . $this->getParam('q') . '%';
		$this->view = new CiviCategorieSuggestiesResponse($this->model->find('type LIKE ?', array($query)));
	}
}
