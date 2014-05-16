<?php

/**
 * DebugLogModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class DebugLogModel extends PersistenceModel {

	const orm = 'LogEntry';

	protected static $instance;

	public function log($module = '', $action = '', array $args = null) {
		$entries = $this->find('moment < ?', array(strtotime('-1 month')));
		foreach ($entries as $entry) {
			$this->delete($entry);
		}
		$entry = new LogEntry();
		$entry->module_action = $module . '->' . $action . '(' . implode(', ', $args) . ')';
		$e = new Exception();
		$entry->call_trace = $e->getTraceAsString();
		$entry->moment = getDateTime();
		$entry->lid_id = LoginLid::instance()->getUid();
		$entry->su_id = LoginLid::instance()->getSuedFrom();
		$entry->ip = $_SERVER['REMOTE_ADDR'];
		$entry->request = $_SERVER['REQUEST_URI'];
		$entry->referer = $_SERVER['HTTP_REFERER'];
		$entry->user_agent = $_SERVER['HTTP_USER_AGENT'];
		$this->create($entry);
		return $entry;
	}

}
