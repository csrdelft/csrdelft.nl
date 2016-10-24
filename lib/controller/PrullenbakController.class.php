<?php

require_once 'model/PrullenbakModel.class.php';
require_once 'view/PrullenbakView.class.php';

/**
 * PrullenbakController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class PrullenbakController extends AclController {

	public function __construct($query) {
		parent::__construct($query, PrullenbakModel::instance());
		switch ($this->getMethod()) {

			case 'GET':
				$this->acl = array(
					'table' => 'P_LOGGED_IN'
				);
				break;

			case 'POST':
				$this->acl = array(
					'data'		 => 'P_LOGGED_IN',
					'restore'	 => 'P_LOGGED_IN',
					'delete'	 => 'P_LOGGED_IN'
				);
				break;
		}
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(2)) { // ID or action
			$this->action = $this->getParam(2);
		} else {
			$this->action = 'table'; // default
		}
		switch ($this->action) {

			// No parameters required
			case 'table':
			case 'data':
				return parent::performAction($args);

			// Selection required
			case 'restore':
			case 'delete':
				$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
				if (!empty($selection)) {
					$args['selection'] = $selection;
					return parent::performAction($args);
				}
			// fall through
		}
		return $this->geentoegang();
	}

	public function GET_table() {
		$table = new PrullenbakTable($this->model);
		$this->view = new CsrLayoutPage($table);
	}

	public function POST_data() {
		$data = array();
		foreach ($this->model->find() as $object) {
			// Check permissions
			if (LoginModel::mag($object->permission_restore) OR LoginModel::mag($object->permission_delete)) {
				$data[] = $object;
			}
		}
		$this->view = new PrullenbakData($data);
	}

	public function POST_restore(array $selection) {
		$data = array();
		foreach ($selection as $UUID) {
			$object = $this->model->retrieveByUUID($UUID);
			if ($object AND ( LoginModel::mag('P_ADMIN') OR LoginModel::mag($object->permission_restore))) {
				$db = Database::instance();
				try {
					$db->beginTransaction();
					$this->model->restoreRecursive($object);
					$db->commit();
					$data[] = $object;
				} catch (Exception $ex) {
					$db->rollBack();
					$this->view = new JsonResponse(array(
						$ex->getFile(),
						$ex->getLine(),
						$ex->getMessage(),
						$ex->getTrace()
							), 500);
					return;
				}
			}
		}
		$this->view = new RemoveRowsResponse($data);
	}

	public function POST_delete(array $selection) {
		$data = array();
		foreach ($selection as $UUID) {
			$object = $this->model->retrieveByUUID($UUID);
			if ($object AND ( LoginModel::mag('P_ADMIN') OR LoginModel::mag($object->permission_delete))) {
				$db = Database::instance();
				try {
					$db->beginTransaction();
					$this->model->deleteRecursive($object);
					$db->commit();
					$data[] = $object;
				} catch (Exception $ex) {
					$db->rollBack();
					$this->view = new JsonResponse(array(
						$ex->getFile(),
						$ex->getLine(),
						$ex->getMessage(),
						$ex->getTrace()
							), 500);
					return;
				}
			}
		}
		$this->view = new RemoveRowsResponse($data);
	}

}
