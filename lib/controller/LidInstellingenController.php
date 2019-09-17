<?php

namespace CsrDelft\controller;

use CsrDelft\model\instellingen\LidInstellingenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\formulier\invoervelden\UrlField;
use CsrDelft\view\JsonResponse;
use Exception;


/**
 * LidInstellingenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class LidInstellingenController {
	private $model;

	public function __construct() {
		$this->model = LidInstellingenModel::instance();
	}

	public function beheer() {
		return view('instellingen.lidinstellingen', [
			'defaultInstellingen' => $this->model->getAll(),
			'instellingen' => $this->model->getAllForLid(LoginModel::getUid())
		]);
	}

	public function update($module, $instelling, $waarde = null) {
		if ($waarde === null) {
			$waarde = filter_input(INPUT_POST, 'waarde', FILTER_SANITIZE_STRING);
		}

		if ($this->model->isValidValue($module, $instelling, urldecode($waarde))) {
			$this->model->wijzigInstelling($module, $instelling, urldecode($waarde));
			return new JsonResponse(['success' => true]);
		} else {
			return new JsonResponse(['success' => false], 400);
		}
	}

	/**
	 * @throws Exception
	 */
	public function opslaan() {
		$this->model->save(); // fetches $_POST values itself
		setMelding('Instellingen opgeslagen', 1);
		$from = new UrlField('referer', HTTP_REFERER, null);
		redirect($from->getValue());
	}

	public function reset($module, $key) {
		$this->model->resetForAll($module, $key);
		setMelding('Voor iedereen de instelling ge-reset naar de standaard waarde', 1);
		return new JsonResponse(true);
	}

}
