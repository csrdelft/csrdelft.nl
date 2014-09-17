<?php

require_once 'MVC/model/PersistenceModel.abstract.php';

/**
 * DebugLogModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class DebugLogModel extends PersistenceModel {

	const orm = 'LogEntry';

	protected static $instance;

	protected function __construct() {
		parent::__construct();
		$entries = $this->find('moment < ?', array(strtotime('-2 months')));
		foreach ($entries as $entry) {
			$this->delete($entry);
		}
	}

	public function log($class, $function, array $args = array(), $dump = null) {
		$entry = new LogEntry();
		$entry->class_function = $class . '->' . $function . '(' . implode(', ', $args) . ')';
		$entry->dump = $dump;
		$e = new Exception();
		$entry->call_trace = $e->getTraceAsString();
		$entry->moment = getDateTime();
		$entry->uid = LoginModel::getUid();
		$entry->su_uid = LoginModel::instance()->getSuedFrom();
		$entry->ip = $_SERVER['REMOTE_ADDR'];
		$entry->request = REQUEST_URI;
		$entry->referer = HTTP_REFERER;
		$entry->user_agent = $_SERVER['HTTP_USER_AGENT'];
		$this->create($entry);
		if (DEBUG AND Database::instance()->inTransaction()) {
			SimpleHTML::setMelding('Debuglog may not be committed: database transaction', 2);
		}
		return $entry;
	}

}
