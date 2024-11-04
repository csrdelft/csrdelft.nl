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
	/**
	 * @param null|string $UUID
	 */
	public function retrieveByUuid(string|null $UUID)
	{
		$metadata = $this->getClassMetadata();

		$parts = explode('@', (string) $UUID, 2);
		$primary_key_values = explode('.', $parts[0]);
		return $this->findOneBy(
			array_combine($metadata->getIdentifierFieldNames(), $primary_key_values)
		);
	}

	/**
	 * @param \CsrDelft\service\Eetplan|null|object $entity
	 */
	public function save(\CsrDelft\service\Eetplan|object|null $entity)
	{
		$this->_em->persist($entity);
		$this->_em->flush();
	}

	/**
	 * @param \CsrDelft\entity\PushAbonnement|\CsrDelft\entity\agenda\AgendaItem|\CsrDelft\entity\documenten\Document|\CsrDelft\entity\eetplan\Eetplan|\CsrDelft\entity\eetplan\EetplanBekenden|\CsrDelft\entity\forum\ForumDeelMelding|mixed|null $entity
	 *
	 * @psalm-param T|\CsrDelft\entity\PushAbonnement|\CsrDelft\entity\agenda\AgendaItem|\CsrDelft\entity\documenten\Document|\CsrDelft\entity\eetplan\Eetplan|\CsrDelft\entity\eetplan\EetplanBekenden|\CsrDelft\entity\forum\ForumDeelMelding|null $entity
	 */
	public function remove($entity)
	{
		$this->_em->remove($entity);
		$this->_em->flush();
	}
}
