<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\controller\framework\QueryParamTrait;
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
 */
class PeilingenController {
	use QueryParamTrait;

	/** @var PeilingenModel */
	private $peilingenModel;
	/** @var PeilingenLogic */
	private $peilingenLogic;

	public function __construct() {
		$this->peilingenModel = PeilingenModel::instance();
		$this->peilingenLogic = PeilingenLogic::instance();
	}

	/**
	 * @param null $id
	 * @return View
	 * @throws CsrGebruikerException
	 */
	public function table($id = null) {
		// Laat een modal zien als een specifieke peiling bewerkt wordt
		if ($id) {
			$table = new PeilingTable();
			$peiling = $this->peilingenModel->find('id = ?', [$id])->fetch();
			$table->setSearch($peiling->titel);
			$form = new PeilingForm($peiling, false);
			$form->setDataTableId($table->getDataTableId());

			return view('default', ['content' => $table, 'modal' => $form]);
		} else {
			return view('default', ['content' => new PeilingTable()]);
		}
	}

	/**
	 * @return View
	 */
	public function lijst() {
		return new PeilingResponse($this->peilingenModel->getPeilingenVoorBeheer());
	}

	/**
	 * @return View
	 * @throws CsrGebruikerException
	 */
	public function nieuw() {
		$peiling = new Peiling();
		$form = new PeilingForm($peiling, true);

		if ($form->isPosted() && $form->validate()) {
			$peiling = $form->getModel();
			$peiling->eigenaar = LoginModel::getUid();
			$peiling->mag_bewerken = false;

			$peiling->id = $this->peilingenModel->create($form->getModel());
			return new PeilingResponse([$peiling]);
		}

		return $form;
	}

	/**
	 * @return View
	 * @throws CsrGebruikerException
	 */
	public function bewerken() {
		$selection = $this->getDataTableSelection();

		if ($selection) {
			$peiling = $this->peilingenModel->retrieveByUUID($selection[0]);

			if (!$this->peilingenModel->magBewerken($peiling)) {
				throw new CsrGebruikerException('Je mag deze peiling niet bewerken!');
			}
		} else {
			// Hier is de id in post gezet
			$peiling = new Peiling();
		}

		$form = new PeilingForm($peiling, false);
		if ($form->isPosted() && $form->validate()) {
			$peiling = $form->getModel();

			$this->peilingenModel->update($peiling);
			return new PeilingResponse([$peiling]);
		}

		return $form;
	}

	/**
	 * @return View
	 */
	public function verwijderen() {
		$selection = $this->getDataTableSelection();
		$peiling = $this->peilingenModel->retrieveByUUID($selection[0]);

		$this->peilingenModel->delete($peiling);

		return new RemoveRowsResponse([$peiling]);
	}

	/**
	 * @param int $id
	 * @return View
	 */
	public function stem($id) {
		$ids = filter_var_array($this->getPost('opties'), FILTER_VALIDATE_INT);

		if($this->peilingenLogic->stem($id, $ids, LoginModel::getUid())) {
			return new JsonResponse(true);
		} else {
			return new JsonResponse(false, 400);
		}
	}
}
