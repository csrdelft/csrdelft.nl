<?php

namespace CsrDelft\repository\fiscaat;

use CsrDelft\entity\fiscaat\CiviCategorie;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * @method CiviCategorie|null find($id, $lockMode = null, $lockVersion = null)
 * @method CiviCategorie|null findOneBy(array $criteria, array $orderBy = null)
 * @method CiviCategorie[]    findAll()
 * @method CiviCategorie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CiviCategorieRepository extends AbstractRepository
{


	/**
	 * @param $query
	 * @return CiviCategorie[]
	 */
	public function suggesties(string $query)
	{
		return $this->createQueryBuilder('cc')
			->where('cc.type LIKE :query')
			->setParameter('query', $query)
			->getQuery()
			->getResult();
	}
}
