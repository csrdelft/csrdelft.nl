<?php

namespace CsrDelft\repository\fiscaat;

use CsrDelft\common\CsrException;
use CsrDelft\entity\fiscaat\CiviPrijs;
use CsrDelft\entity\fiscaat\CiviProduct;
use CsrDelft\repository\AbstractRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * @method CiviPrijs|null find($id, $lockMode = null, $lockVersion = null)
 * @method CiviPrijs|null findOneBy(array $criteria, array $orderBy = null)
 * @method CiviPrijs[]    findAll()
 * @method CiviPrijs[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CiviPrijsRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, CiviPrijs::class);
	}

	/**
	 * Verwijderd alle prijzen voor een product zonder klagen. PAS DUS OP.
	 *
	 * Moet in een transaction aangeroepen worden.
	 *
	 * @param CiviProduct $product
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function verwijderVoorProduct(CiviProduct $product)
	{
		if (!$this->_em->getConnection()->isTransactionActive()) {
			throw new CsrException(
				'Kan geen product verwijderen als je niet in een transactie zit!'
			);
		}

		$prijzen = $this->findBy(['product_id' => $product->id]);

		foreach ($prijzen as $prijs) {
			$this->_em->remove($prijs);
		}
		$this->_em->flush();
	}
}
