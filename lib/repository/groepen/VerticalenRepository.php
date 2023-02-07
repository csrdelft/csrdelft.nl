<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\common\Util\SqlUtil;
use CsrDelft\entity\groepen\Verticale;
use CsrDelft\repository\GroepRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

class VerticalenRepository extends GroepRepository
{
	public function getEntityClassName()
	{
		return Verticale::class;
	}

	public function get($letter)
	{
		if ($verticale = $this->findOneBy(['letter' => $letter])) {
			return $verticale;
		}

		return parent::get($letter);
	}

	public function findBy(
		array $criteria,
		array $orderBy = null,
		$limit = null,
		$offset = null
	) {
		return parent::findBy(
			$criteria,
			['letter' => 'ASC'] + ($orderBy ?? []),
			$limit,
			$offset
		);
	}

	/**
	 * @param $naam
	 * @return Verticale|null
	 * @throws NonUniqueResultException
	 */
	public function searchByNaam($naam)
	{
		return $this->createQueryBuilder('v')
			->where('v.naam LIKE :naam')
			->setParameter('naam', SqlUtil::sql_contains($naam))
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();
	}

	public function nieuw($soort = null)
	{
		/** @var Verticale $verticale */
		$verticale = parent::nieuw();
		$verticale->letter = null;
		return $verticale;
	}
}
