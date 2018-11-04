<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\controller\framework\AclController;
use CsrDelft\model\entity\peilingen\Peiling;
use CsrDelft\model\peilingen\PeilingenLogic;
use CsrDelft\model\peilingen\PeilingenModel;
use CsrDelft\model\peilingen\PeilingOptiesModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\formulier\datatable\RemoveRowsResponse;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\peilingen\PeilingBeheerTable;
use CsrDelft\view\peilingen\PeilingForm;
use CsrDelft\view\peilingen\PeilingResponse;
use CsrDelft\view\View;

/**
 * Class PeilingenController
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * @property PeilingenModel $model
 */
class PeilingenController extends AclController {

	/**
	 * @var PeilingOptiesModel
	 */
	private $query;

	public function __construct($query) {
		parent::__construct($query, PeilingenModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'beheer' => 'P_PEILING_MOD',
				'opties' => 'P_PEILING_MOD',
				'verwijderen' => 'P_PEILING_MOD',
			);
		} else {
			$this->acl = array(
				'beheer' => 'P_PEILING_MOD',
				'stem' => 'P_PEILING_VOTE',
				'bewerken' => 'P_PEILING_MOD',
				'nieuw' => 'P_PEILING_MOD',
				'verwijderen' => 'P_PEILING_MOD',
				'opties' => 'P_PEILING_VOTE',
				'stem2' => 'P_PEILING_VOTE',
			);
		}
		$this->query = $query;
	}

	public function performAction(array $args = array()) {
		$this->action = $this->getParam(2);
		$args = $this->getParams(3);
		$this->view = parent::performAction($args);
	}

	public function GET_beheer($id = null) {
		$table = new PeilingBeheerTable();
		$view = new CsrLayoutPage($table);

		if ($id) {
			$peiling = $this->model->find('id = ?', [$id])->fetch();
			$table->filter = $peiling->titel;
			$view->modal = new PeilingForm($peiling, false);
		}

		return $view;
	}

	public function POST_beheer() {
		return new PeilingResponse($this->model->find());
	}

	public function POST_nieuw() {
		$peiling = new Peiling();
		$form = new PeilingForm($peiling, true);

		if ($form->isPosted() && $form->validate()) {
			$peiling = $form->getModel();
			$peiling->eigenaar = LoginModel::getUid();
			$peiling->mag_bewerken = false;

			$peiling->id = $this->model->create($form->getModel());
			return new PeilingResponse([$peiling]);
		}

		return $form;
	}

	public function POST_bewerken() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		$peiling = $this->model->retrieveByUUID($selection[0]);
		$form = new PeilingForm($peiling, false);

		if ($form->isPosted() && $form->validate()) {
			$peiling = $form->getModel();

			$this->model->update($peiling);
			return new PeilingResponse([$peiling]);
		}

		return $form;
	}

	/**
	 * @return View
	 * @throws CsrException
	 * @throws CsrGebruikerException
	 * @throws CsrToegangException
	 */
	public function opties() {
		$router = new PeilingOptiesController($this->query);
		$router->performAction();

		return $router->view;
	}

	public function verwijderen() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		$peiling = $this->model->retrieveByUUID($selection[0]);

		$this->model->delete($peiling);

		return new RemoveRowsResponse([$peiling]);
	}

	public function stem2($id) {
		$inputJSON = file_get_contents('php://input');
		$input = json_decode($inputJSON, TRUE);

		$ids = filter_var_array($input['opties'], FILTER_VALIDATE_INT);

		if(PeilingenLogic::instance()->stem($id, $ids, LoginModel::getUid())) {
			return new JsonResponse(true);
		} else {
			return new JsonResponse(false, 400);
		}
	}

	public function stem() {
		$peiling_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
		$optie = filter_input(INPUT_POST, 'optie', FILTER_VALIDATE_INT);
		// optie en id zijn null of false als filter_input faalt
		if (is_numeric($peiling_id) && is_numeric($optie)) {
			$this->model->stem($peiling_id, $optie);
			redirect(HTTP_REFERER . '#peiling' . $peiling_id);
		} else {
			setMelding("Kies een optie om op te stemmen", 0);
		}

		redirect(HTTP_REFERER);
	}

}
