<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrToegangException;
use CsrDelft\model\entity\security\AccessControl;
use CsrDelft\model\security\AccessModel;
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
	 * @var AccessModel
	 */
	private $accessModel;

	public function __construct(AccessModel $accessModel) {
		$this->accessModel = $accessModel;
	}

	public function bekijken($environment = null, $resource = null) {
		return view('default', [
			'content' => new RechtenTable($this->accessModel, $environment, $resource)
		]);
	}

	public function data($environment = null, $resource = null) {
		return new RechtenData($this->accessModel->getTree($environment, $resource));
	}

	public function aanmaken($environment = null, $resource = null) {
		$ac = $this->accessModel->nieuw($environment, $resource);
		$form = new RechtenForm($ac, 'aanmaken');
		if ($form->validate()) {
			$this->accessModel->setAcl($ac->environment, $ac->resource, array(
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
		$ac = $this->accessModel->retrieveByUUID($selection[0]);
		$form = new RechtenForm($ac, 'wijzigen');
		if ($form->validate()) {
			$this->accessModel->setAcl($ac->environment, $ac->resource, array(
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
			$ac = $this->accessModel->retrieveByUUID($UUID);
			$this->accessModel->setAcl($ac->environment, $ac->resource, array(
				$ac->action => null
			));
			$response[] = $ac;
		}
		return new RemoveRowsResponse($response);
	}

}
