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
	private $model;

	public function __construct() {
		$this->model = AccessModel::instance();
	}

	public function bekijken($environment = null, $resource = null) {
		return view('default', [
			'content' => new RechtenTable($this->model, $environment, $resource)
		]);
	}

	public function data($environment = null, $resource = null) {
		return new RechtenData($this->model->getTree($environment, $resource));
	}

	public function aanmaken($environment = null, $resource = null) {
		$ac = $this->model->nieuw($environment, $resource);
		$form = new RechtenForm($ac, 'aanmaken');
		if ($form->validate()) {
			$this->model->setAcl($ac->environment, $ac->resource, array(
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
		$ac = $this->model->retrieveByUUID($selection[0]);
		$form = new RechtenForm($ac, 'wijzigen');
		if ($form->validate()) {
			$this->model->setAcl($ac->environment, $ac->resource, array(
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
			$ac = $this->model->retrieveByUUID($UUID);
			$this->model->setAcl($ac->environment, $ac->resource, array(
				$ac->action => null
			));
			$response[] = $ac;
		}
		return new RemoveRowsResponse($response);
	}

}
