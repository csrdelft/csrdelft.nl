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
class CiviProductRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, CiviProduct::class);
	}

	/**
	 * @param string ...$cie
	 * @return CiviProduct[]
	 */
	public function findByCie(...$cie): mixed
	{
		return $this->createQueryBuilder('civi_product')
			->join('civi_product.categorie', 'categorie')
			->where('categorie.cie in (:cie)')
			->setParameter('cie', $cie)
			->orderBy('civi_product.prioriteit', 'desc')
			->getQuery()
			->getResult();
	}

	/**
	 * @param int $id
	 *
	 * @return CiviProduct
	 */
	public function getProduct($id): ?CiviProduct
	{
		$product = $this->find($id);
		$product->tmpPrijs = $product->getPrijs()->prijs;

		return $product;
	}

	/**
	 * @param $query
	 * @return CiviProduct[]
	 */
	public function getSuggesties($query): mixed
	{
		return $this->createQueryBuilder('cp')
			->where('cp.beschrijving LIKE :query')
			->setParameter('query', $query)
			->getQuery()
			->getResult();
	}

	/**
	 * @param CiviProduct $product
	 * @return string last insert id
	 */
	public function create(CiviProduct $product)
	{
		return $this->_em->transactional(function () use ($product) {
			$this->_em->persist($product);

			$prijs = new CiviPrijs();
			$prijs->product = $product;
			$prijs->van = date_create_immutable('now');
			$prijs->tot = null;
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
	public function update(CiviProduct $product)
	{
		return $this->_em->transactional(function () use ($product) {
			$nu = date_create_immutable('now');

			$prijs = $product->getPrijs();
			// Alleen prijs updaten als deze veranderd is, niet als alleen andere velden veranderen.
			if ($prijs->prijs !== $product->tmpPrijs) {
				$prijs->tot = $nu;

				$nieuw_prijs = new CiviPrijs();
				$nieuw_prijs->product = $product;
				$nieuw_prijs->van = $nu;
				$nieuw_prijs->tot = null;
				$nieuw_prijs->prijs = $product->tmpPrijs;

				$product->prijzen->add($nieuw_prijs);

				$this->_em->persist($nieuw_prijs);
			}

			$this->_em->flush();
		});
	}
}
