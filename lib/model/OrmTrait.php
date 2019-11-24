<?php


namespace CsrDelft\model;

use CsrDelft\Orm\Persistence\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * Voeg standaard methodes van csrdelft/orm toe aan een doctrine repository.
 *
 * Voor gebruik in een EntityServiceRepository
 * @package CsrDelft\model
 */
trait OrmTrait {

	/**
	 * Alias voor de doctrine find methode.
	 *
	 * @param $id
	 * @param null $lockMode
	 * @param null $lockVersion
	 * @return object|null
	 */
	public function doctrineFind($id, $lockMode = null, $lockVersion = null) {
		return parent::find($id, $lockMode, $lockVersion);
	}

	public function update($entity) {
		/** @var EntityManager $entityManager */
		$entityManager = $this->getEntityManager();
		$entityManager->persist($entity);
		$entityManager->flush();
	}

	public function create($entity) {
		/** @var EntityManager $entityManager */
		$entityManager = $this->getEntityManager();
		/** @var ClassMetadata $metadata */
		$metadata = $this->getClassMetadata();

		$entityManager->persist($entity);
		$entityManager->flush();

		$identifier = $metadata->getIdentifierValues($entity);

		// Return id als er een enkele id is, anders return alles.
		if (count($metadata->getIdentifier()) == 1) {
			return $identifier[$metadata->getIdentifier()[0]];
		} else {
			return $identifier;
		}
	}

	public function delete($entity) {
		/** @var EntityManager $entityManager */
		$entityManager = $this->getEntityManager();
		$entityManager->remove($entity);
		$entityManager->flush();
	}

	public function count($criteria, $criteria_params) {
		/** @var EntityManager $entityManager */
		$entityManager = $this->getEntityManager();
		/** @var ClassMetadata $metadata */
		$metadata = $this->getClassMetadata();

		$queryBuilder = new QueryBuilder();
		$countQuery = $queryBuilder->buildSelect(
			['COUNT(*)'],
			$metadata->getTableName(),
			$criteria
		);

		$rsm = new ResultSetMapping();
		$rsm->addScalarResult('COUNT(*)', 'count', 'integer');

		return (int)$entityManager->createNativeQuery($countQuery, $rsm)
			->setParameters($criteria_params)
			->getSingleScalarResult();
	}

	public function exists($entity) {
		/** @var ClassMetadata $metadata */
		$metadata = $this->getClassMetadata();

		return parent::find($metadata->getIdentifierValues($entity)) !== null;
	}

	/**
	 * De oude find methode uit csrdelft/orm
	 *
	 * @param null $criteria
	 * @param array $criteria_params
	 * @param null $group_by
	 * @param null $order_by
	 * @param null $limit
	 * @param int $start
	 * @return array
	 */
	public function ormFind(
		$criteria = null,
		$criteria_params = [],
		$group_by = null,
		$order_by = null,
		$limit = null,
		$start = 0
	) {
		/** @var EntityManager $entityManager */
		$entityManager = $this->getEntityManager();
		/** @var ClassMetadata $metadata */
		$metadata = $this->getClassMetadata();

		$rsm = new ResultSetMappingBuilder($entityManager);
		$rsm->addRootEntityFromClassMetadata($metadata->getName(), 'u');

		$query = (new QueryBuilder())->buildSelect(
			['u.*'],
			$metadata->getTableName() . ' u',
			$criteria,
			$group_by,
			$order_by,
			$limit,
			$start
		);

		return $entityManager
			->createNativeQuery($query, $rsm)
			->setParameters($criteria_params)
			->getResult();
	}

	public function retrieveByUuid($UUID) {
		/** @var ClassMetadata $metadata */
		$metadata = $this->getClassMetadata();

		$parts = explode('@', $UUID, 2);
		$primary_key_values = explode('.', $parts[0]);
		return $this->findOneBy(array_combine($metadata->getIdentifierFieldNames(), $primary_key_values));
	}
}
