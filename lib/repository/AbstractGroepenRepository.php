<?php

namespace CsrDelft\repository;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\groepen\AbstractGroep;
use CsrDelft\entity\groepen\GroepStatus;
use CsrDelft\model\security\AccessModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Persistence\Database;
use Doctrine\Persistence\ManagerRegistry;
use ReflectionClass;
use ReflectionProperty;

/**
 * AbstractGroepenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * @method AbstractGroep|null find($id, $lockMode = null, $lockVersion = null)
 * @method AbstractGroep|null findOneBy(array $criteria, array $orderBy = null)
 * @method AbstractGroep[]    findAll()
 */
abstract class AbstractGroepenRepository extends AbstractRepository {
	/**
	 * @var AccessModel
	 */
	private $accessModel;
	/**
	 * @var AbstractGroep
	 */
	public $entityClass;
	/**
	 * @var Database
	 */
	private $database;

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

		$this->database = ContainerFacade::getContainer()->get(Database::class);
	}

	public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null) {
		return parent::findBy($criteria, ['begin_moment' => 'DESC'] + ($orderBy ?? []), $limit, $offset);
	}

	public static function getUrl() {
		return '/groepen/' . static::getNaam();
	}

	public static function getNaam() {
		return strtolower(str_replace('Repository', '', classNameZonderNamespace(get_called_class())));
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

	/**
	 * Set primary key.
	 *
	 * @param PersistentEntity|AbstractGroep $groep
	 * @return void
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
	public function create(AbstractGroep $groep) {
		$this->_em->persist($groep);
		$this->_em->flush();
	}

	/**
	 * @param AbstractGroep $groep
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
	public function update(AbstractGroep $groep) {
		$this->_em->persist($groep);
		$this->_em->flush();
	}

	/**
	 * @param AbstractGroep $groep
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
	public function delete(AbstractGroep $groep) {
		$this->_em->remove($groep);
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
				$ledenmodel = $this->_em->getRepository($newgroep->getLidType());
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
	 * @param null $soort
	 * @return AbstractGroep
	 */
	public function nieuw(/* @noinspection PhpUnusedParameterInspection */ $soort = null) {
		$orm = $this->entityClass;
		$groep = new $orm();
		$groep->naam = null;
		$groep->familie = null;
		$groep->status = GroepStatus::HT();
		$groep->samenvatting = '';
		$groep->omschrijving = null;
		$groep->begin_moment = null;
		$groep->eind_moment = null;
		$groep->website = null;
		$groep->maker_uid = LoginModel::getUid();
		return $groep;
	}

	/**
	 * Return groepen by GroepStatus voor lid.
	 *
	 * @param string $uid
	 * @param GroepStatus|array $status
	 * @return AbstractGroep[]
	 */
	public function getGroepenVoorLid($uid, $status = null) {
		$qb = $this->createQueryBuilder('ag')
			->orderBy('ag.begin_moment', 'DESC')
			->join('ag.leden', 'l')
			->where('l.uid = :uid')
			->setParameter('uid', $uid);

		if (is_array($status)) {
			$qb->andWhere('ag.status in (:status)')
				->setParameter('status', $status);
		} elseif ($status) {
			$qb->andWhere('ag.status = :status')
				->setParameter('status', $status);
		}

		return $qb->getQuery()->getResult();
	}

}
