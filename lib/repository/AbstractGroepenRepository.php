<?php

namespace CsrDelft\repository;

use CsrDelft\entity\groepen\AbstractGroep;
use CsrDelft\entity\groepen\GroepStatus;
use CsrDelft\model\security\AccessModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use Doctrine\Persistence\ManagerRegistry;
use PDO;
use ReflectionClass;
use ReflectionProperty;

/**
 * AbstractGroepenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * @method AbstractGroep|null find($id, $lockMode = null, $lockVersion = null)
 * @method AbstractGroep|null findOneBy(array $criteria, array $orderBy = null)
 * @method AbstractGroep[]    findAll()
 * @method AbstractGroep[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
abstract class AbstractGroepenRepository extends AbstractRepository {
	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'begin_moment DESC';
	/**
	 * @var AccessModel
	 */
	private $accessModel;
	private $entityClass;

	/**
	 * AbstractGroepenModel constructor.
	 * @param AccessModel $accessModel
	 * @param ManagerRegistry $managerRegistry
	 * @param $entityClass
	 */
	public function __construct(AccessModel $accessModel, ManagerRegistry $managerRegistry, $entityClass) {
		parent::__construct($managerRegistry, $entityClass);

		$this->accessModel = $accessModel;
		$this->entityClass = $entityClass;
	}

	/**
	 * @param $id
	 * @return AbstractGroep|false
	 */
	public function get($id) {
		if (is_numeric($id)) {
			return $this->find($id);
		}
		return $this->findOneBy(['familie' => $id, 'status' => GroepStatus::HT()]);
	}

	public static function getNaam() {
		return strtolower(str_replace('Repository', '', classNameZonderNamespace(get_called_class())));
	}

	public static function getUrl() {
		return '/groepen/' . static::getNaam();
	}

	/**
	 * @param null $soort
	 * @return AbstractGroep
	 */
	public function nieuw(/* @noinspection PhpUnusedParameterInspection */$soort = null) {
		$orm = $this->entityClass;
		$groep = new $orm();
		$groep->naam = null;
		$groep->familie = null;
		$groep->status = GroepStatus::HT;
		$groep->samenvatting = '';
		$groep->omschrijving = null;
		$groep->begin_moment = null;
		$groep->eind_moment = null;
		$groep->website = null;
		$groep->maker_uid = LoginModel::getUid();
		return $groep;
	}

	/**
	 * Set primary key.
	 *
	 * @param PersistentEntity|AbstractGroep $groep
	 * @return void
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
	public function create(PersistentEntity $groep) {
		$this->_em->persist($groep);
		$this->_em->flush();
	}

	/**
	 * Converteer groep inclusief leden van klasse.
	 *
	 * @param AbstractGroep $oldgroep
	 * @param AbstractGroepenRepository $oldmodel
	 * @param string $soort
	 * @return AbstractGroep|bool
	 */
	public function converteer(AbstractGroep $oldgroep, AbstractGroepenRepository $oldmodel, $soort = null) {
		try {
			return $this->_em->transactional(function () use ($oldgroep, $oldmodel, $soort) {
				// groep converteren
				$newgroep = $this->nieuw($soort);
				$rc = new ReflectionClass($newgroep);
				foreach ($rc->getProperties(ReflectionProperty::IS_PUBLIC) as $attribute => $value) {
					if (property_exists($newgroep, $value->getName())) {
						$newgroep->{$value->getName()} = $oldgroep->{$value->getName()};
					}
				}
				$newgroep->id = null;
				$this->_em->persist($newgroep);

				// leden converteren
				$ledenmodel = $newgroep::getLedenModel();
				foreach ($oldgroep->getLeden() as $oldlid) {
					$newlid = $ledenmodel->nieuw($newgroep, $oldlid->uid);
					$oldlidRc = new ReflectionClass($oldlid);
					foreach ($oldlidRc->getProperties(ReflectionProperty::IS_PUBLIC) as $attribute => $value) {
						if (property_exists($newlid, $value->getName())) {
							$newlid->{$value->getName()} = $oldgroep->{$value->getName()};
						}
					}
					$newlid->groep_id = $newgroep->id;
					$this->_em->persist($newlid);
				}

				// leden verwijderen
				foreach ($oldgroep->getLeden() as $oldlid) {
					$this->_em->remove($oldlid);
				}

				// groep verwijderen
				$this->_em->remove($oldgroep);
				$this->_em->flush();

				return $newgroep;
			});
		} catch (\Throwable $ex) {
			setMelding($ex->getMessage(), -1);
			return false;
		}
	}

	/**
	 * Return groepen by GroepStatus voor lid.
	 *
	 * @param string $uid
	 * @param GroepStatus|array $status
	 * @return AbstractGroep[]
	 */
	public function getGroepenVoorLid($uid, $status = null) {
		/** @var AbstractGroep $orm */
		$orm = static::ORM;
		$ids = $this->database->sqlSelect(['DISTINCT groep_id'], $orm::getLedenModel()->getTableName(), 'uid = ?', [$uid])->fetchAll(PDO::FETCH_COLUMN);
		if (empty($ids)) {
			return [];
		}
		$where = 'id IN (' . implode(', ', array_fill(0, count($ids), '?')) . ')';
		if ($status === null) {
			return $this->prefetch($where, $ids);
		} elseif (is_array($status)) {
			$where .= ' AND status IN (' . implode(', ', array_fill(0, count($status), '?')) . ')';
			return $this->prefetch($where, array_merge($ids, $status));
		}
		$where .= ' AND status = ?';
		$ids[] = $status;
		return $this->prefetch($where, $ids);
	}

}
