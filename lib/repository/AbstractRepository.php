<?php


namespace CsrDelft\repository;


use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Class AbstractRepository
 *
 * Repository met wat handige tools er in.
 *
 * @package CsrDelft\repository
 */
abstract class AbstractRepository extends ServiceEntityRepository {
	public function retrieveByUuid($UUID) {
		/** @var ClassMetadata $metadata */
		$metadata = $this->getClassMetadata();

		$parts = explode('@', $UUID, 2);
		$primary_key_values = explode('.', $parts[0]);
		return $this->findOneBy(array_combine($metadata->getIdentifierFieldNames(), $primary_key_values));
	}
}
