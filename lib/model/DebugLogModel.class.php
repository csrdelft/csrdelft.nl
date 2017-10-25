<?php

namespace CsrDelft\model;

use function CsrDelft\getDateTime;
use CsrDelft\model\entity\DebugLogEntry;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\PersistenceModel;
use function CsrDelft\setMelding;

/**
 * DebugLogModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class DebugLogModel extends PersistenceModel {

	const ORM = DebugLogEntry::class;

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
		if (LoginModel::instance()->isSued()) {
			$entry->su_uid = LoginModel::getSuedFrom()->uid;
		}
		$entry->ip = $_SERVER['REMOTE_ADDR'];
		$entry->referer = $_SERVER['HTTP_REFERER'];
		$entry->request = REQUEST_URI;
		$entry->ip_referer = HTTP_REFERER;
		$entry->user_agent = $_SERVER['HTTP_USER_AGENT'];
		$entry->id = $this->create($entry);
		if (DEBUG AND Database::instance()->getDatabase()->inTransaction()) {
			setMelding('Debug log may not be committed: database transaction', 2);
			setMelding($dump, 0);
		}
		return $entry;
	}

}
