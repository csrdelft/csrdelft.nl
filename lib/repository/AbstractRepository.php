<?php


namespace CsrDelft\repository;


use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * Class AbstractRepository
 *
 * Repository met wat handige tools er in.
 *
 * @package CsrDelft\repository
 */
abstract class AbstractRepository extends ServiceEntityRepository
{
	public function retrieveByUuid($UUID)
	{
		$metadata = $this->getClassMetadata();

		$parts = explode('@', $UUID, 2);
		$primary_key_values = explode('.', $parts[0]);
		return $this->findOneBy(array_combine($metadata->getIdentifierFieldNames(), $primary_key_values));
	}

	public function save($entity)
	{
		$this->_em->persist($entity);
		$this->_em->flush();
	}

	public function remove($entity)
	{
		$this->_em->remove($entity);
		$this->_em->flush();
	}
}
