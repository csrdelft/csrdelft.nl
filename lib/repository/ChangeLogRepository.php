<?php

namespace CsrDelft\repository;

use CsrDelft\entity\ChangeLogEntry;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ChangeLogModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @method ChangeLogEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChangeLogEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChangeLogEntry[]    findAll()
 * @method ChangeLogEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChangeLogRepository extends AbstractRepository {
	/**
	 * @var LoginModel
	 */
	private $loginModel;

	/**
	 * ChangeLogModel constructor.
	 * @param ManagerRegistry $registry
	 * @param LoginModel $loginModel
	 */
	public function __construct(ManagerRegistry $registry, LoginModel $loginModel) {
		parent::__construct($registry, ChangeLogEntry::class);

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
		$change->moment = date_create();
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
		$this->getEntityManager()->persist($change);
		$this->getEntityManager()->flush();
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
