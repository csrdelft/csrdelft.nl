<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrToegangException;
use CsrDelft\repository\instellingen\InstellingenRepository;
use CsrDelft\model\security\LoginModel;


/**
 * InstellingenBeheerController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class InstellingenBeheerController {
	/**
	 * @var InstellingenRepository
	 */
	private $instellingenRepository;

	public function __construct(InstellingenRepository $instellingenRepository) {
		$this->instellingenRepository = $instellingenRepository;
	}

	protected function assertToegang($module = null) {
		if (!$this->mag($module)) {
			throw new CsrToegangException();
		}
	}

	protected function mag($module = null) {
		if ($module) {
			switch ($module) {
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
		$this->assertToegang($module);

		if (in_array($module, $this->instellingenRepository->getModules())) {
			$instellingen = $this->instellingenRepository->getModuleKeys($module);
		} else {
			$instellingen = null;
			$module = null;
		}

		return view('instellingenbeheer.beheer', [
			'module' => $module,
			'modules' => $this->instellingenRepository->getModules(),
			'instellingen' => $instellingen,
		]);
	}

	public function opslaan($module, $id) {
		$this->assertToegang($module);

		$waarde = filter_input(INPUT_POST, 'waarde', FILTER_UNSAFE_RAW);
		$instelling = $this->instellingenRepository->wijzigInstelling($module, $id, $waarde);

		return view('instellingenbeheer.regel', [
			'waarde' => $instelling->waarde,
			'id' => $instelling->instelling_id,
			'module' => $instelling->module,
		]);
	}

	public function reset($module, $id) {
		$this->assertToegang($module);

		$instelling = $this->instellingenRepository->wijzigInstelling($module, $id, $this->instellingenRepository->getDefault($module, $id));

		return view('instellingenbeheer.regel', [
			'waarde' => $instelling->waarde,
			'id' => $instelling->instelling_id,
			'module' => $instelling->module,
		]);
	}

}
