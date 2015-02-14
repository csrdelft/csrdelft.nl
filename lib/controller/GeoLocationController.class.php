<?php

require_once 'model/GeoLocationModel.class.php';
//require_once 'view/GeoLocationView.class.php';

/**
 * GeoLocationController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GeoLocationController extends AclController {

	public function __construct($query) {
		parent::__construct($query, GeoLocationModel::instance());
		if (!$this->isPosted()) {
			$this->acl = array(
					//'map' => 'P_ADMIN'
			);
		} else {
			$this->acl = array(
				'save' => 'P_LOGGED_IN'
			);
		}
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		parent::performAction($this->getParams(3));
	}

	public function save() {
		$position = filter_input(INPUT_POST, 'position', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		$location = $this->model->savePosition(LoginModel::getUid(), $position);
		$this->view = new JsonResponse($location);
	}

	public function map() {
		//TODO
	}

}
