<?php

/**
 * ChangeLogModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class ChangeLogModel extends PersistenceModel {

	const orm = 'ChangeLogEntry';

	protected static $instance;

	public function log($subject, $property, $old, $new) {
		$last = $this->find('subject = ? AND property = ?', array($subject, $property), 'id DESC', null, 1)->fetch();
		$log = new HappieStatusLog();
		$log->moment = getDateTime();
		if ($last) {
			$log->elapsed = strtotime($log->moment) - strtotime($last->moment);
		} else {
			$log->elapsed = null;
		}
		$log->subject = $subject;
		$log->property = $property;
		$log->old_value = $old;
		$log->new_value = $new;
		$log->uid = LoginModel::getUid();
		if (LoginModel::instance()->isSued()) {
			$log->su_uid = LoginModel::instance()->getSuedFrom()->getUid();
		}
		$log->id = $this->create($log);
		return $log;
	}

}
