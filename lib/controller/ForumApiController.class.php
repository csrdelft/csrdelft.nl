<?php

/**
 * ForumApiController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller of the forum JSON API.
 * 
 */
class ForumApiController extends AclController {

	public function __construct($query) {
		parent::__construct($query, ForumModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'indeling' => 'P_FORUM_READ'
			);
		}
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		} else {
			$this->action = 'indeling';
		}
		parent::performAction($this->getParams(3));
	}

	public function GET_indeling() {
		$this->view = new JsonResponse($this->model->getForumIndelingVoorLid());
	}

}
