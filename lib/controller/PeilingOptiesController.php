<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\controller\framework\AclController;
use CsrDelft\model\entity\peilingen\PeilingOptie;
use CsrDelft\model\peilingen\PeilingenLogic;
use CsrDelft\model\peilingen\PeilingOptiesModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\datatable\RemoveRowsResponse;
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
	private $peilingenLogic;

	public function __construct($query)
	{
		parent::__construct($query, PeilingOptiesModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = [
				'opties' => P_PEILING_EDIT,
			];
		} else {
			$this->acl = [
				'opties' => P_PEILING_VOTE,
				'toevoegen' => P_PEILING_VOTE,
				'verwijderen' => P_PEILING_EDIT,
			];
		}

		$this->peilingenLogic = PeilingenLogic::instance();
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
		return new PeilingOptieResponse($this->peilingenLogic->getOptionsAsJson($id, LoginModel::getUid()));
	}

	/**
	 * @param $id
	 * @return PeilingOptieForm|PeilingOptieResponse
	 * @throws CsrGebruikerException
	 */
	public function POST_toevoegen($id) {
		$form = new PeilingOptieForm(new PeilingOptie(), $id);

		if (!$this->peilingenLogic->magOptieToevoegen($id)) {
			throw new CsrGebruikerException("Mag geen opties meer toevoegen!");
		}

		if ($form->isPosted() && $form->validate()) {
			/** @var PeilingOptie $optie */
			$optie = $form->getModel();
			$optie->ingebracht_door = LoginModel::getUid();
			$optie->peiling_id = $id;
			$optie->id = $this->model->create($optie);
			return new PeilingOptieResponse([$optie]);
		}

		return $form;
	}

	/**
	 * @param $id
	 * @return RemoveRowsResponse
	 * @throws CsrGebruikerException
	 */
	public function POST_verwijderen($id = null) {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);

		/** @var PeilingOptie $peilingOptie */
		$peilingOptie = $this->model->retrieveByUUID($selection[0]);

		if ($peilingOptie !== false && $peilingOptie->stemmen == 0) {
			$this->model->delete($peilingOptie);
			return new RemoveRowsResponse([$peilingOptie]);
		} else {
			throw new CsrGebruikerException('Peiling optie bestaat niet of er is al een keer op gestemd.');
		}
	}
}
