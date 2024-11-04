<?php

namespace CsrDelft\repository\documenten;

use CsrDelft\entity\documenten\Document;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * @method Document|null find($id, $lockMode = null, $lockVersion = null)
 * @method Document|null findOneBy(array $criteria, array $orderBy = null)
 * @method Document[]    findAll()
 * @method Document[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentRepository extends AbstractRepository
{


	/**
	 * @param $id
	 *
	 * @return Document|false
	 */
	public function get(string $id)
	{
		return $this->find($id);
	}

	/**
	 * @param null|string $zoekterm
	 * @param int $limiet
	 *
	 * @return Document[]
	 */
	public function zoek(string|null $zoekterm, $limiet = null)
	{
		return $this->createQueryBuilder('d')
			->where('MATCH(d.naam, d.filename) AGAINST (:zoekterm) > 0')
			->setParameter('zoekterm', $zoekterm)
			->setMaxResults($limiet)
			->getQuery()
			->getResult();
	}
}
