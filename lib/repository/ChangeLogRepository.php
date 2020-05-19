<?php

namespace CsrDelft\repository;

use CsrDelft\entity\ChangeLogEntry;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\service\security\LoginService;
use CsrDelft\service\security\SuService;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\Persistence\ManagerRegistry;
use function common\short_class;

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
	 * @var SuService
	 */
	private $suService;

	/**
	 * ChangeLogModel constructor.
	 * @param ManagerRegistry $registry
	 * @param SuService $suService
	 */
	public function __construct(ManagerRegistry $registry, SuService $suService) {
		parent::__construct($registry, ChangeLogEntry::class);

		$this->suService = $suService;
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
	 * @param string|mixed $subject
	 * @param string $property
	 * @param string $old
	 * @param string $new
	 *
	 * @return ChangeLogEntry
	 */
	public function nieuw($subject, $property, $old, $new) {
		$change = new ChangeLogEntry();
		$change->moment = date_create_immutable();
		try {
			$meta = $this->_em->getClassMetadata(get_class($subject));
			$change->subject = implode(".", $meta->getIdentifierValues($subject)) . '@' . strtolower(short_class(get_class($subject))) . '.csrdelft.nl';
		} catch (MappingException $ex) {
			// ignore
			if ($subject instanceof PersistentEntity) {
				$change->subject = $subject->getUUID();
			} else {
				$change->subject = $subject;
			}
		}

		$change->property = $property;
		$change->old_value = $old;
		$change->new_value = $new;
		if ($this->suService->isSued()) {
			$change->uid = $this->suService::getSuedFrom()->uid;
		} else {
			$change->uid = LoginService::getUid();
		}
		return $change;
	}

	/**
	 * @param ChangeLogEntry $change
	 * @return void
	 */
	public function create(ChangeLogEntry $change) {
		$this->getEntityManager()->persist($change);
		$this->getEntityManager()->flush();
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
