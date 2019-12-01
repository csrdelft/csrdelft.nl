<?php

namespace CsrDelft\model;

use CsrDelft\model\entity\DebugLogEntry;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\PersistenceModel;

/**
 * DebugLogModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class DebugLogModel extends PersistenceModel {

	const ORM = DebugLogEntry::class;
	/**
	 * @var LoginModel
	 */
	private $loginModel;

	public function __construct(LoginModel $loginModel) {
		parent::__construct();

		$this->loginModel = $loginModel;
	}

	/**
	 */
	public function opschonen() {
		$entries = $this->find('moment < ?', array(strtotime('-2 months')));
		foreach ($entries as $entry) {
			$this->delete($entry);
		}
	}

	/**
	 * @param string $class
	 * @param string $function
	 * @param string[] array $args
	 * @param string $dump
	 *
	 * @return DebugLogEntry
	 */
	public function log($class, $function, array $args = array(), $dump = null) {
		$entry = new DebugLogEntry();
		$entry->class_function = $class . '->' . $function . '(' . implode(', ', $args) . ')';
		$entry->dump = $dump;
		$exception = new \Exception();
		$entry->call_trace = $exception->getTraceAsString();
		$entry->moment = getDateTime();
		$entry->uid = LoginModel::getUid();
		if ($this->loginModel->isSued()) {
			$entry->su_uid = LoginModel::getSuedFrom()->uid;
		}
		$entry->ip = @$_SERVER['REMOTE_ADDR'] ?: '127.0.0.1';
		$entry->referer = @$_SERVER['HTTP_REFERER'] ?: 'CLI';
		$entry->request = REQUEST_URI ?: 'CLI';
		$entry->ip_referer = HTTP_REFERER ?: '';
		$entry->user_agent = @$_SERVER['HTTP_USER_AGENT'] ?: 'CLI';
		$entry->id = $this->create($entry);
		if (DEBUG AND $this->database->getDatabase()->inTransaction()) {
			setMelding('Debug log may not be committed: database transaction', 2);
			setMelding($dump, 0);
		}
		return $entry;
	}

}
