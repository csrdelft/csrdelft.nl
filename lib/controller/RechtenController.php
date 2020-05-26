<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrToegangException;
use CsrDelft\common\datatable\RemoveDataTableEntry;
use CsrDelft\entity\security\AccessControl;
use CsrDelft\repository\security\AccessRepository;
use CsrDelft\view\RechtenForm;
use CsrDelft\view\RechtenTable;


/**
 * RechtenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van de ACL.
 */
class RechtenController extends AbstractController {
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
		return $this->tableData($this->accessRepository->getTree($environment, $resource));
	}

	public function aanmaken($environment = null, $resource = null) {
		$ac = $this->accessRepository->nieuw($environment, $resource);
		$form = new RechtenForm($ac, 'aanmaken');
		if ($form->validate()) {
			$this->accessRepository->setAcl($ac->environment, $ac->resource, [$ac->action => $ac->subject]);
			return $this->tableData([$ac]);
		} else {
			return $form;
		}
	}

	public function wijzigen() {
		$selection = $this->getDataTableSelection();

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
			return $this->tableData([$ac]);
		} else {
			return $form;
		}
	}

	public function verwijderen() {
		$selection = $this->getDataTableSelection();
		$response = [];

		foreach ($selection as $UUID) {
			/** @var AccessControl $ac */
			$ac = $this->accessRepository->retrieveByUUID($UUID);
			$response[] = new RemoveDataTableEntry(explode('@', $UUID)[0], AccessControl::class);
			$this->accessRepository->setAcl($ac->environment, $ac->resource, [$ac->action => null]);
		}

		return $this->tableData($response);
	}

}
