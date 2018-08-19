<?php

namespace CsrDelft\controller;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\entity\peilingen\Peiling;
use CsrDelft\model\entity\peilingen\PeilingOptie;
use CsrDelft\model\peilingen\PeilingenModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\peilingen\PeilingenBeheerView;

/**
 * Class PeilingenController
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * @property PeilingenModel $model
 */
class PeilingenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, PeilingenModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'beheer' => 'P_PEILING_MOD',
				'verwijderen' => 'P_PEILING_MOD',
			);
		} else {
			$this->acl = array(
				'beheer' => 'P_PEILING_MOD',
				'stem' => 'P_PEILING_VOTE',
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = $this->getParam(2);
		if ($this->action == 'verwijderen') {
			$args = $this->getParams(3);
		}
		$this->view = parent::performAction($args);
	}

	public function beheer() {
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

	public function verwijderen($peiling_id) {
		$peiling = $this->model->getPeilingById((int)$peiling_id);
		if ($peiling === false) {
			setMelding('Peiling al verwijderd!', 2);
		} else {
			$this->model->delete($peiling);
			setMelding('Peiling is verwijderd!', 1);
		}

		redirect('/peilingen/beheer');
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
