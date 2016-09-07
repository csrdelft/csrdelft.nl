<?php

/**
 * ChangeLogModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class ChangeLogModel extends PersistenceModel {

	const ORM = 'ChangeLogEntry';

	protected static $instance;

	public function nieuw($subject, $property, $old, $new) {
		$change = new ChangeLogEntry();
		$change->moment = getDateTime();
		if ($subject instanceof PersistentEntity) {
			$change->subject = $subject->getUUID();
		} else {
			$change->subject = $subject;
		}
		$change->property = $property;
		$change->old_value = $old;
		$change->new_value = $new;
		if (LoginModel::instance()->isSued()) {
			$change->uid = LoginModel::getSuedFrom()->uid;
		} else {
			$change->uid = LoginModel::getUid();
		}
		return $change;
	}

	public function create(PersistentEntity $change) {
		$change->id = (int) parent::create($change);
	}

	public function log($subject, $property, $old, $new) {
		$change = $this->nieuw($subject, $property, $old, $new);
		$this->create($change);
		return $change;
	}

	public function logChanges(array $diff) {
		foreach ($diff as $change) {
			$this->create($change);
		}
	}

}
