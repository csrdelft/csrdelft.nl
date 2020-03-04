<?php

namespace CsrDelft\controller;

use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\view\JsonResponse;
use Exception;


/**
 * LidInstellingenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class LidInstellingenController extends AbstractController {
	/** @var LidInstellingenRepository  */
	private $lidInstellingenRepository;

	public function __construct(LidInstellingenRepository $lidInstellingenRepository) {
		$this->lidInstellingenRepository = $lidInstellingenRepository;
	}

	public function beheer() {
		return view('instellingen.lidinstellingen', [
			'defaultInstellingen' => $this->lidInstellingenRepository->getAll(),
			'instellingen' => $this->lidInstellingenRepository->getAllForLid(LoginModel::getUid())
		]);
	}

	public function update($module, $instelling, $waarde = null) {
		if ($waarde === null) {
			$waarde = filter_input(INPUT_POST, 'waarde', FILTER_SANITIZE_STRING);
		}

		if ($this->lidInstellingenRepository->isValidValue($module, $instelling, urldecode($waarde))) {
			$this->lidInstellingenRepository->wijzigInstelling($module, $instelling, urldecode($waarde));
			return new JsonResponse(['success' => true]);
		} else {
			return new JsonResponse(['success' => false], 400);
		}
	}

	/**
	 * @throws Exception
	 */
	public function opslaan() {
		$this->lidInstellingenRepository->save(); // fetches $_POST values itself
		setMelding('Instellingen opgeslagen', 1);
		return $this->redirectToRoute('lidinstellingen-beheer');
	}

	public function reset($module, $key) {
		$this->lidInstellingenRepository->resetForAll($module, $key);
		setMelding('Voor iedereen de instelling ge-reset naar de standaard waarde', 1);
		return new JsonResponse(true);
	}

}
