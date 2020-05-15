<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrToegangException;
use CsrDelft\entity\security\AccessControl;
use CsrDelft\repository\security\AccessRepository;
use CsrDelft\view\datatable\RemoveRowsResponse;
use CsrDelft\view\RechtenData;
use CsrDelft\view\RechtenForm;
use CsrDelft\view\RechtenTable;


/**
 * RechtenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van de ACL.
 */
class RechtenController {
	/**
	 * @var AccessRepository
	 */
	private $accessRepository;

	public function __construct(AccessRepository $accessRepository) {
		$this->accessRepository = $accessRepository;
	}

	public function bekijken($environment = null, $resource = null) {
		return view('default', [
			'content' => new RechtenTable($this->accessRepository, $environment, $resource)
		]);
	}

	public function data($environment = null, $resource = null) {
		return new RechtenData($this->accessRepository->getTree($environment, $resource));
	}

	public function aanmaken($environment = null, $resource = null) {
		$ac = $this->accessRepository->nieuw($environment, $resource);
		$form = new RechtenForm($ac, 'aanmaken');
		if ($form->validate()) {
			$this->accessRepository->setAcl($ac->environment, $ac->resource, array(
				$ac->action => $ac->subject
			));
			return new RechtenData(array($ac));
		} else {
			return $form;
		}
	}

	public function wijzigen() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		if (!isset($selection[0])) {
			throw new CsrToegangException();
		}
		/** @var AccessControl $ac */
		$ac = $this->accessRepository->retrieveByUUID($selection[0]);
		$form = new RechtenForm($ac, 'wijzigen');
		if ($form->validate()) {
			$this->accessRepository->setAcl($ac->environment, $ac->resource, array(
				$ac->action => $ac->subject
			));
			return new RechtenData(array($ac));
		} else {
			return $form;
		}
	}

	public function verwijderen() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		$response = array();
		foreach ($selection as $UUID) {
			/** @var AccessControl $ac */
			$ac = $this->accessRepository->retrieveByUUID($UUID);
			$this->accessRepository->setAcl($ac->environment, $ac->resource, array(
				$ac->action => null
			));
			$response[] = $ac;
		}
		return new RemoveRowsResponse($response);
	}

}
