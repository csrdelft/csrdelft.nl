<?php

namespace CsrDelft\controller;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\entity\peilingen\Peiling;
use CsrDelft\model\entity\peilingen\PeilingOptie;
use CsrDelft\model\peilingen\PeilingenModel;
use CsrDelft\model\peilingen\PeilingOptiesModel;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\formulier\datatable\RemoveRowsResponse;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\peilingen\PeilingOptieResponse;
use CsrDelft\view\peilingen\PeilingResponse;
use CsrDelft\view\peilingen\PeilingBeheerTable;
use CsrDelft\view\peilingen\PeilingenBeheerView;
use CsrDelft\view\peilingen\PeilingForm;
use CsrDelft\view\peilingen\PeilingOptieTable;

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
	private $peilingOptiesModel;
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
				'opties' => 'P_PEILING_MOD',
			);
		}
		$this->peilingOptiesModel = PeilingOptiesModel::instance();
		$this->query = $query;
	}

	public function performAction(array $args = array()) {
		$this->action = $this->getParam(2);
		$args = $this->getParams(3);
		$this->view = parent::performAction($args);
	}

	public function GET_beheer() {
		return new CsrLayoutPage(new PeilingBeheerTable());


		$peiling = new Peiling();

		if ($this->getMethod() == 'POST') {
			$peiling->tekst = filter_input(INPUT_POST, 'verhaal', FILTER_SANITIZE_STRING);
			$peiling->titel = filter_input(INPUT_POST, 'titel', FILTER_SANITIZE_STRING);
			$opties = filter_input(INPUT_POST, 'opties', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);

			if (count($opties) > 0) {
				foreach ($opties as $optie_tekst) {
					if (trim($optie_tekst) != '') {
						$peilingOptie = new PeilingOptie();
						$peilingOptie->optie = $optie_tekst;
						$peiling->nieuwOptie($peilingOptie);
					}
				}
			}

			if (($errors = PeilingenModel::instance()->validate($peiling)) != '') {
				setMelding($errors, -1);
			} else {
				$peiling_id = PeilingenModel::instance()->create($peiling);
				setMelding('Peiling is aangemaakt', 1);

				// Voorkom dubbele submit
				redirect(HTTP_REFERER . "#peiling" . $peiling_id);
			}
		}

		$view = new CsrLayoutPage(new PeilingenBeheerView($this->model->getLijst(), $peiling));
		$view->addCompressedResources('peilingbeheer');

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
