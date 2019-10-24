<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrGebruikerException;
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
class PeilingOptiesController extends AbstractController {
	/** @var PeilingenLogic */
	private $peilingenLogic;
	/** @var PeilingOptiesModel */
	private $peilingOptiesModel;

	public function __construct() {
		$this->peilingOptiesModel = PeilingOptiesModel::instance();
		$this->peilingenLogic = PeilingenLogic::instance();
	}

	public function table($id) {
		return new PeilingOptieTable($id);
	}

	public function lijst($id) {
		return new PeilingOptieResponse($this->peilingenLogic->getOptionsAsJson($id, LoginModel::getUid()));
	}

	/**
	 * @param $id
	 * @return PeilingOptieForm|PeilingOptieResponse
	 * @throws CsrGebruikerException
	 */
	public function toevoegen($id) {
		$form = new PeilingOptieForm(new PeilingOptie(), $id);

		if (!$this->peilingenLogic->magOptieToevoegen($id)) {
			throw new CsrGebruikerException("Mag geen opties meer toevoegen!");
		}

		if ($form->isPosted() && $form->validate()) {
			/** @var PeilingOptie $optie */
			$optie = $form->getModel();
			$optie->ingebracht_door = LoginModel::getUid();
			$optie->peiling_id = $id;
			$optie->id = $this->peilingOptiesModel->create($optie);
			return new PeilingOptieResponse([$optie]);
		}

		return $form;
	}

	/**
	 * @return RemoveRowsResponse
	 * @throws CsrGebruikerException
	 */
	public function verwijderen() {
		$selection = $this->getDataTableSelection();

		/** @var PeilingOptie $peilingOptie */
		$peilingOptie = $this->peilingOptiesModel->retrieveByUUID($selection[0]);

		if ($peilingOptie !== false && $peilingOptie->stemmen == 0) {
			$this->peilingOptiesModel->delete($peilingOptie);
			return new RemoveRowsResponse([$peilingOptie]);
		} else {
			throw new CsrGebruikerException('Peiling optie bestaat niet of er is al een keer op gestemd.');
		}
	}
}
