<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrToegangException;
use CsrDelft\controller\framework\QueryParamTrait;
use CsrDelft\model\instellingen\InstellingenModel;
use CsrDelft\model\security\LoginModel;


/**
 * InstellingenBeheerController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class InstellingenBeheerController {
	use QueryParamTrait;

	private $model;

	public function __construct() {
		$this->model = InstellingenModel::instance();

		if (!$this->mag()) {
			throw new CsrToegangException();
		}
	}

	protected function mag() {
		if ($this->hasParam(3)) {
			switch ($this->getParam(3)) {
				case 'agenda':
					return LoginModel::mag(P_AGENDA_MOD);
				case 'corvee':
					return LoginModel::mag(P_CORVEE_MOD);
				case 'maaltijden':
					return LoginModel::mag(P_MAAL_MOD);
				default:
					return LoginModel::mag(P_ADMIN);
			}
		}
		return true; // hoofdpagina: geen module
	}

	public function module($module = null) {
		if (in_array($module, $this->model->getModules())) {
			$instellingen = $this->model->getModuleKeys($module);
		} else {
			$instellingen = null;
			$module = null;
		}

		return view('instellingenbeheer.beheer', [
			'module' => $module,
			'modules' => $this->model->getModules(),
			'instellingen' => $instellingen,
		]);
	}

	public function opslaan($module, $id) {
		$waarde = filter_input(INPUT_POST, 'waarde', FILTER_UNSAFE_RAW);
		$instelling = $this->model->wijzigInstelling($module, $id, $waarde);

		return view('instellingenbeheer.regel', [
			'waarde' => $instelling->waarde,
			'id' => $instelling->instelling_id,
			'module' => $instelling->module,
		]);
	}

	public function reset($module, $id) {
		$instelling = $this->model->wijzigInstelling($module, $id, $this->model->getDefault($module, $id));

		return view('instellingenbeheer.regel', [
			'waarde' => $instelling->waarde,
			'id' => $instelling->instelling_id,
			'module' => $instelling->module,
		]);
	}

}
