<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\controller\framework\AclController;
use CsrDelft\model\entity\peilingen\Peiling;
use CsrDelft\model\peilingen\PeilingenLogic;
use CsrDelft\model\peilingen\PeilingenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\datatable\RemoveRowsResponse;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\peilingen\PeilingForm;
use CsrDelft\view\peilingen\PeilingResponse;
use CsrDelft\view\peilingen\PeilingTable;
use CsrDelft\view\View;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * @property PeilingenModel $model
 */
class PeilingenController extends AclController {

	/**
	 * @var string
	 */
	private $query;

	public function __construct($query) {
		parent::__construct($query, PeilingenModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'beheer' => P_PEILING_EDIT,
				'opties' => P_PEILING_EDIT,
				'verwijderen' => P_PEILING_MOD,
			);
		} else {
			$this->acl = array(
				'beheer' => P_PEILING_EDIT,
				'stem' => P_PEILING_VOTE,
				'bewerken' => P_PEILING_EDIT,
				'nieuw' => P_PEILING_EDIT,
				'verwijderen' => P_PEILING_MOD,
				'opties' => P_PEILING_VOTE,
			);
		}
		$this->query = $query;
	}

	public function performAction(array $args = array()) {
		$this->action = $this->getParam(2);
		$args = $this->getParams(3);
		$this->view = parent::performAction($args);
	}

	/**
	 * @param null $id
	 * @return View
	 * @throws CsrGebruikerException
	 */
	public function GET_beheer($id = null) {
		// Laat een modal zien als een specifieke peiling bewerkt wordt
		if ($id) {
			$table = new PeilingTable();
			$peiling = $this->model->find('id = ?', [$id])->fetch();
			$table->setSearch($peiling->titel);
			$form = new PeilingForm($peiling, false);
			$form->setDataTableId($table->getDataTableId());

			return view('default', [
				'titel' => 'Peilingen beheer',
				'content' => $table,
				'modal' => $form
			]);
		} else {
			return view('default', [
				'titel' => 'Peilingen beheer',
				'content' => new PeilingTable(),
			]);
		}
	}

	/**
	 * @return View
	 */
	public function POST_beheer() {
		return new PeilingResponse($this->model->getPeilingenVoorBeheer());
	}

	/**
	 * @return View
	 * @throws CsrGebruikerException
	 */
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

	/**
	 * @return View
	 * @throws CsrGebruikerException
	 */
	public function POST_bewerken() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);

		if ($selection) {
			$peiling = $this->model->retrieveByUUID($selection[0]);

			if (!$this->model->magBewerken($peiling)) {
				throw new CsrGebruikerException('Je mag deze peiling niet bewerken!');
			}
		} else {
			// Hier is de id in post gezet
			$peiling = new Peiling();
		}

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

	/**
	 * @return View
	 */
	public function verwijderen() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		$peiling = $this->model->retrieveByUUID($selection[0]);

		$this->model->delete($peiling);

		return new RemoveRowsResponse([$peiling]);
	}

	/**
	 * @param int $id
	 * @return View
	 */
	public function stem($id) {
		$inputJSON = file_get_contents('php://input');
		$input = json_decode($inputJSON, TRUE);

		$ids = filter_var_array($input['opties'], FILTER_VALIDATE_INT);

		if(PeilingenLogic::instance()->stem($id, $ids, LoginModel::getUid())) {
			return new JsonResponse(true);
		} else {
			return new JsonResponse(false, 400);
		}
	}
}
