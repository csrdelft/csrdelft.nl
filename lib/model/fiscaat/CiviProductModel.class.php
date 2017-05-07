<?php

namespace CsrDelft\model\fiscaat;

use CsrDelft\model\entity\fiscaat\CiviPrijs;
use CsrDelft\model\entity\fiscaat\CiviProduct;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\PersistenceModel;
use DateTime;

require_once 'model/entity/fiscaat/CiviProduct.class.php';
require_once 'model/fiscaat/CiviPrijsModel.class.php';
require_once 'model/fiscaat/CiviCategorieModel.class.php';

/**
 * Class CiviProductModel
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class CiviProductModel extends PersistenceModel {
	const ORM = CiviProduct::class;
	const DIR = 'fiscaat/';

	protected static $instance;

	/**
	 * @param CiviProduct $product
	 * @return CiviPrijs
	 */
	public function getPrijs($product) {
		return CiviPrijsModel::instance()->find(
			'product_id = ?',
			$product->getValues(true),
			null,
			'van DESC',
			1
		)->fetch();
	}

	public function getProduct($id) {
		/** @var CiviProduct $product */
		$product = $this->retrieveByPrimaryKey(array($id));

		$product->prijs = $this->getPrijs($product)->prijs;

		return $product;
	}

	public function find($criteria = null, array $criteria_params = array(), $group_by = null, $order_by = null, $limit = null, $start = 0) {
		/** @var CiviProduct[] $entries */
		$entries = parent::find($criteria, $criteria_params, $group_by, $order_by, $limit, $start);

		foreach ($entries as $entry) {
			$entry->prijs = $this->getPrijs($entry)->prijs;
			yield $entry;
		}
	}

	/**
	 * @param PersistentEntity|CiviProduct $product
	 * @return string last insert id
	 */
	public function create(PersistentEntity $product) {
		return Database::transaction(function () use ($product) {
			$product->id = parent::create($product);

			$prijs = new CiviPrijs();
			$prijs->product_id = $product->id;
			$prijs->van = date_create('now')->format(DateTime::ISO8601);
			$prijs->tot = NULL;
			$prijs->prijs = $product->prijs;

			CiviPrijsModel::instance()->create($prijs);

			return $product->id;
		});
	}

	/**
	 * @param PersistentEntity|CiviProduct $product
	 * @return int number of rows affected
	 */
	public function update(PersistentEntity $product) {
		return Database::transaction(function () use ($product) {
			$nu = date_create('now')->format(DateTime::ISO8601);

			/** @var CiviPrijs $prijs */
			$prijs = $this->getPrijs($product);
			// Alleen prijs updaten als deze veranderd is, niet als alleen andere velden veranderen.
			if ($prijs->prijs !== $product->prijs) {
				$prijs->tot = $nu;
				CiviPrijsModel::instance()->update($prijs);

				$nieuw_prijs = new CiviPrijs();
				$nieuw_prijs->product_id = $product->id;
				$nieuw_prijs->van = $nu;
				$nieuw_prijs->tot = NULL;
				$nieuw_prijs->prijs = $product->prijs;
				CiviPrijsModel::instance()->create($nieuw_prijs);
			}

			return parent::update($product);
		});
	}
}
