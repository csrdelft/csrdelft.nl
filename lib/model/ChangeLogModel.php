<?php

namespace CsrDelft\model;

use CsrDelft\model\entity\ChangeLogEntry;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\PersistenceModel;

/**
 * ChangeLogModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class ChangeLogModel extends PersistenceModel {

	const ORM = ChangeLogEntry::class;
	/**
	 * @var LoginModel
	 */
	private $loginModel;

	/**
	 * ChangeLogModel constructor.
	 * @param LoginModel $loginModel
	 */
	public function __construct(LoginModel $loginModel) {
		parent::__construct();

		$this->loginModel = $loginModel;
	}

	/**
	 * @param string $subject
	 * @param string $property
	 * @param string $old
	 * @param string $new
	 *
	 * @return ChangeLogEntry
	 */
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
		if ($this->loginModel->isSued()) {
			$change->uid = $this->loginModel::getSuedFrom()->uid;
		} else {
			$change->uid = $this->loginModel::getUid();
		}
		return $change;
	}

	/**
	 * @param ChangeLogEntry|PersistentEntity $change
	 * @return void
	 */
	public function create(PersistentEntity $change) {
		$change->id = (int)parent::create($change);
	}

	/**
	 * @param string $subject
	 * @param string $property
	 * @param string $old
	 * @param string $new
	 *
	 * @return ChangeLogEntry
	 */
	public function log($subject, $property, $old, $new) {
		$change = $this->nieuw($subject, $property, $old, $new);
		$this->create($change);
		return $change;
	}

	/**
	 * @param ChangeLogEntry[] $diff
	 */
	public function logChanges(array $diff) {
		foreach ($diff as $change) {
			$this->create($change);
		}
	}

}
