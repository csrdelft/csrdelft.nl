<?php

namespace CsrDelft\controller;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\entity\peilingen\PeilingOptie;
use CsrDelft\model\peilingen\PeilingOptiesModel;
use CsrDelft\view\peilingen\PeilingOptieForm;
use CsrDelft\view\peilingen\PeilingOptieResponse;
use CsrDelft\view\peilingen\PeilingOptieTable;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/10/2018
 *
 * Voor routes in /peilingen/opties
 */
class PeilingOptiesController extends AclController
{
	public function __construct($query)
	{
		parent::__construct($query, PeilingOptiesModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = [
				'opties' => 'P_PEILING_MOD',
			];
		} else {
			$this->acl = [
				'opties' => 'P_PEILING_MOD',
				'toevoegen' => 'P_PEILING_MOD',
			];
		}
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(4)) {
			$this->action = $this->getParam(4);
			$args = $this->getParams(5);
		} else {
			$this->action = 'opties';
			$args = [];
		}

		$id = $this->getParam(3);

		array_unshift($args, $id);

		$this->view = parent::performAction($args);
	}

	public function GET_opties($id) {
		return new PeilingOptieTable($id);
	}

	public function POST_opties($id) {
		return new PeilingOptieResponse($this->model->find('peiling_id = ?', [$id]));
	}

	public function POST_toevoegen($id) {
		$form = new PeilingOptieForm(new PeilingOptie(), $id);

		if ($form->isPosted() && $form->validate()) {
			$optie = $form->getModel();
			$this->model->create($optie);
			return new PeilingOptieResponse($optie);
		}

		return $form;
	}
}
