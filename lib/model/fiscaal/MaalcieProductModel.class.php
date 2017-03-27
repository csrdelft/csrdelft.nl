<?php

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\PersistenceModel;

require_once 'model/entity/fiscaal/MaalcieProduct.class.php';
require_once 'model/fiscaal/MaalciePrijsModel.class.php';

class MaalcieProductModel extends PersistenceModel {
	const ORM = 'MaalcieProduct';
	const DIR = 'fiscaal/';

	protected static $instance;

	/**
	 * @param MaalcieProduct $product
	 * @return MaalciePrijs
	 */
	public function getPrijs($product) {
		return MaalciePrijsModel::instance()->find('productid = ?', $product->getValues(true), null, 'van DESC', 1)->fetch();
	}

	public function getProduct($id) {
		/** @var MaalcieProduct $product */
		$product = $this->retrieveByPrimaryKey(array($id));

		$product->prijs = $this->getPrijs($product)->prijs;

		return $product;
	}

	public function find($criteria = null, array $criteria_params = array(), $group_by = null, $order_by = null, $limit = null, $start = 0) {
		/** @var MaalcieProduct[] $entries */
		$entries = parent::find($criteria, $criteria_params, $group_by, $order_by, $limit, $start)->fetchAll();

		foreach ($entries as $entry) {
			$entry->prijs = $this->getPrijs($entry)->prijs;
		}

		return $entries;
	}

	/**
	 * @param PersistentEntity|MaalcieProduct $product
	 * @return string last insert id
	 */
	public function create(PersistentEntity $product) {
		return Database::transaction(function () use ($product) {
			$product->id = parent::create($product);

			$prijs = new MaalciePrijs();
			$prijs->productid = $product->id;
			$prijs->van = date_create('now')->format(DateTime::ISO8601);
			$prijs->tot = date_create('0000-00-00')->format(DateTime::ISO8601);
			$prijs->prijs = $product->prijs;

			MaalciePrijsModel::instance()->create($prijs);

			return $product->id;
		});
	}

	/**
	 * @param PersistentEntity|MaalcieProduct $product
	 * @return int number of rows affected
	 */
	public function update(PersistentEntity $product) {
		return Database::transaction(function () use ($product) {
			$nu = date_create('now')->format(DateTime::ISO8601);

			/** @var MaalciePrijs $prijs */
			$prijs = $this->getPrijs($product);
			$prijs->tot = $nu;
			MaalciePrijsModel::instance()->update($prijs);

			$nieuw_prijs = new MaalciePrijs();
			$nieuw_prijs->productid = $product->id;
			$nieuw_prijs->van = $nu;
			$nieuw_prijs->tot = date_create('0000-00-00')->format(DateTime::ISO8601);
			$nieuw_prijs->prijs = $product->prijs;
			MaalciePrijsModel::instance()->create($nieuw_prijs);

			return parent::update($product);
		});
	}
}
