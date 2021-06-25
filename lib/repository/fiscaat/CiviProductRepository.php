<?php

namespace CsrDelft\repository\fiscaat;

use CsrDelft\entity\fiscaat\CiviPrijs;
use CsrDelft\entity\fiscaat\CiviProduct;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * @method CiviProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method CiviProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method CiviProduct[]    findAll()
 * @method CiviProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CiviProductRepository extends AbstractRepository {
	/**
	 * @var CiviPrijsRepository
	 */
	private $civiPrijsRepository;

	public function __construct(ManagerRegistry $registry, CiviPrijsRepository $civiPrijsRepository) {
		parent::__construct($registry, CiviProduct::class);

		$this->civiPrijsRepository = $civiPrijsRepository;
	}

	/**
	 * @param int $id
	 *
	 * @return CiviProduct
	 */
	public function getProduct($id) {
		$product = $this->find($id);
		$product->tmpPrijs = $product->getPrijs()->prijs;

		return $product;
	}

	/**
	 * @param $query
	 * @return CiviProduct[]
	 */
	public function getSuggesties($query) {
		return $this->createQueryBuilder('cp')
			->where('cp.beschrijving LIKE :query')
			->setParameter('query', $query)
			->getQuery()->getResult();
	}

	/**
	 * @param CiviProduct $product
	 * @return string last insert id
	 */
	public function create(CiviProduct $product) {
		return $this->_em->transactional(function () use ($product) {
			$this->_em->persist($product);

			$prijs = new CiviPrijs();
			$prijs->product = $product;
			$prijs->van = date_create_immutable('now');
			$prijs->tot = NULL;
			$prijs->prijs = $product->tmpPrijs;

			$product->prijzen->add($prijs);

			$this->_em->persist($prijs);

			$this->_em->flush();

			return $product->id;
		});
	}

	/**
	 * @param CiviProduct $product
	 * @return int number of rows affected
	 */
	public function update(CiviProduct $product) {
		return $this->_em->transactional(function () use ($product) {
			$nu = date_create_immutable('now');

			$prijs = $product->getPrijs();
			// Alleen prijs updaten als deze veranderd is, niet als alleen andere velden veranderen.
			if ($prijs->prijs !== $product->tmpPrijs) {
				$prijs->tot = $nu;

				$nieuw_prijs = new CiviPrijs();
				$nieuw_prijs->product = $product;
				$nieuw_prijs->van = $nu;
				$nieuw_prijs->tot = NULL;
				$nieuw_prijs->prijs = $product->tmpPrijs;

				$product->prijzen->add($nieuw_prijs);

				$this->_em->persist($nieuw_prijs);
			}

			$this->_em->flush();
		});
	}
}
